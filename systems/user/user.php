<?php

class user
{
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
			return;
		}

		if ($savedToken !== $token) {
			$token = self::setToken();
            session::setFlashMessage('Invalid login token. Try again.', 'error');
			template::setKeyword('user.login', 'token', $token);
			template::serveTemplate('user.login');
			return;
		}

		try {
			$user = self::checkLoginDetails($username, $userpassword);
		} catch (Exception $e) {
			$token = self::setToken();
            session::setFlashMessage('The username or password are incorrect. Try again.', 'error');
			template::setKeyword('user.login', 'token', $token);
			template::serveTemplate('user.login');
			return;
		}

		session::set('user', $user);

		$originalpage = session::get('viewPage');
		url::redirect($originalpage);
        return;
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

		return $user['user_id'];
	}
}

/* vim: set expandtab ts=4 sw=4: */
