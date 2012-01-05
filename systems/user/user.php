<?php

class user
{
    /**
     * Number of minutes to lock someone out from too many
     * attempts to log in.
     */
    private static $_lockTimeLimit = 5;

    public static function process()
    {
        template::serveTemplate('user.header');
        template::display();

        if (empty($_POST) === TRUE) {
            $token = self::setToken();
            template::setKeyword('user.login', 'token', $token);
            template::serveTemplate('user.login');
            template::display();
        } else {
            self::authCheck();
        }
        template::serveTemplate('footer');
        template::display();
    }

    /**
     * Authentication checking goes here.
     */
    private static function authCheck()
    {
        if (self::_isLockedOut(FALSE) === TRUE) {
            $token = self::setToken();
            session::setFlashMessage('You have been locked out for too many attempted logins.', 'error');
            template::setKeyword('user.login', 'token', $token);
            template::serveTemplate('user.login');
            return;
        }

        $options = array('username', 'userpassword', 'token');

        foreach ($options as $option) {
            $$option = '';
            if (isset($_POST[$option]) === FALSE) {
                continue;
            }

            if (empty($_POST[$option]) === FALSE) {
                $$option = $_POST[$option];
            }
        }

        try {
            $savedToken = session::get('login.token');
        } catch (Exception $e) {
            $token = self::setToken();
            session::setFlashMessage('Invalid login token. Try again.', 'error');
            template::setKeyword('user.login', 'token', $token);
            template::serveTemplate('user.login');
            self::_isLockedOut(TRUE);
            return;
        }

        if ($savedToken !== $token) {
            $token = self::setToken();
            session::setFlashMessage('Invalid login token. Try again.', 'error');
            template::setKeyword('user.login', 'token', $token);
            template::serveTemplate('user.login');
            self::_isLockedOut(TRUE);
            return;
        }

        try {
            $user = self::checkLoginDetails($username, $userpassword);
        } catch (Exception $e) {
            $token = self::setToken();
            session::setFlashMessage('The username or password are incorrect. Try again.', 'error');
            template::setKeyword('user.login', 'token', $token);
            template::serveTemplate('user.login');
            self::_isLockedOut(TRUE);
            return;
        }

        session::set('user', $user);

        $originalpage = session::get('viewPage');
        url::redirect($originalpage);
        return;
    }

    private static function _isLockedOut($update=TRUE)
    {
        $ip     = self::_getIp();
        $sql    = "select attempts from ".db::getPrefix()."user_login_locks where ip=:ip and NOW() between start_time AND end_time";
        $query  = db::select($sql, array($ip));
        $result = db::fetch($query);

        if ($update === FALSE) {
            if (empty($result) === TRUE) {
                return FALSE;
            }
            if ($result['attempts'] <= 2) {
                return FALSE;
            }
            return TRUE;
        }

        if (empty($result) === TRUE) {
            $sql    = "insert into ".db::getPrefix()."user_login_locks(ip, start_time, end_time, attempts) values (:ip, :start_time, :end_time, :attempts)";
            $now    = date('r');
            $values = array(
                    ':ip'         => $ip,
                    ':start_time' => $now,
                    ':end_time'   => date('r', strtotime($now.' + '.self::$_lockTimeLimit.' minutes')),
                    ':attempts'   => 1,
                    );
            $result = db::execute($sql, $values);
            return;
        }

        $sql    = "update ".db::getPrefix()."user_login_locks set attempts = attempts + 1 where ip=:ip and now() between start_time and end_time";
        $values = array(
                ':ip' => $ip,
                );
        $result = db::execute($sql, $values);
    }

    private static function _getIp()
    {
        $ip = '';
        if (isset($_SERVER['X_FORWARDED_FOR']) === TRUE) {
            $addrs = explode(',',$_SERVER['X_FORWARDED_FOR']);
            $ip    = array_pop($addrs);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return trim($ip);
    }

    private static function setToken()
    {
        $token = sha1(uniqid(rand(), TRUE));
        session::set('login.token', $token);
        return $token;
    }

    private static function checkLoginDetails($username, $password)
    {
        $sql   = "select user_id from ".db::getPrefix()."users where username=:username and passwd=:password and useractive='y'";
        $query = db::select($sql, array($username, sha1($password)));
        $user  = db::fetch($query);

        if (empty($user) === TRUE) {
            throw new Exception("Unable to authenticate user");
        }

        $sql    = "delete from ".db::getPrefix()."user_login_locks WHERE ip=:ip";
        $values = array(
                ':ip' => self::_getIp(),
                );
        $result = db::execute($sql, $values);
        return $user['user_id'];
    }
}

/* vim: set expandtab ts=4 sw=4: */
