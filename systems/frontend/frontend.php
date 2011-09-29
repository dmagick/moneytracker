<?php
class frontend
{

	public static function display()
	{

        template::serveTemplate('header');
        template::display();

        if (isset($_SERVER['PATH_INFO']) === TRUE) {
            $info = trim($_SERVER['PATH_INFO'], '/');
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
