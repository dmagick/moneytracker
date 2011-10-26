<?php
class frontend
{

	public static function display()
	{

        $page = '';
        if (isset($_SERVER['PATH_INFO']) === TRUE) {
            $page = trim($_SERVER['PATH_INFO'], '/');
        }

        if (session::has('user') === FALSE) {
            if (session::has('viewPage') === FALSE) {
                session::set('viewPage', $page);
            }
            user::process();
            return;
        }

        template::serveTemplate('header');
        template::display();

        if (session::has('viewPage') === TRUE) {
            $page = session::get('viewPage');
            session::remove('viewPage');
        }

        if (empty($page) === FALSE) {
            $info = trim($page, '/');
            $bits = explode('/', $info);
            if (empty($bits[0]) === FALSE) {
                $system = array_shift($bits);
                $bits   = implode('/', $bits);
                if (isValidSystem($system) === TRUE) {
                    call_user_func_array(array($system, 'process'), array($bits));
                }
            }
        }

        template::serveTemplate('footer');
        template::display();
	}
}

/* vim: set expandtab ts=4 sw=4: */
