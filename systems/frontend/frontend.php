<?php
/**
 * Frontend class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

/**
 * The frontend class.
 * Works out which page you are trying to view and processes it.
 * Could hand off requests to other systems if it needs to.
 *
 * @package money
 */
class frontend
{

    /**
     * Display a page.
     *
     * If the user hasn't logged in, it remembers the page you are trying
     * to view, takes you to the login page, then if that works, redirects
     * the user back to the original page.
     *
     * @return void
     *
     * @uses isValidSystem
     * @uses session::get
     * @uses session::has
     * @uses session::remove
     * @uses session::set
     * @uses template::display
     * @uses template::serveTemplate
     * @uses user::process
     */
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
