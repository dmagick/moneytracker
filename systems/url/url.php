<?php
/**
 * URL class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

/**
 * The url class.
 * Handles redirecting to a particular page prefixed with the system url.
 *
 * @package money
 */
class url
{

    /**
     * The system url. Redirecting is done based on this (with a page
     * name at the end).
     *
     * @uses url::getUrl
     * @uses url::redirect
     * @uses url::setUrl
     *
     * @static
     */
    private static $_url = NULL;

    /**
     * Redirect to another page with a header redirect.
     *
     * @param string  $page  The page to redirect to.
     * @param boolean $admin Whether it's in the admin area or not.
     *
     * @uses getUrl
     *
     * @return void
     * @throw  exception Throws an exception if the url hasn't been
     *                   set previously.
     *
     * @static
     */
    public static function redirect($page, $admin=FALSE)
    {
        try {
            $url = self::getUrl().'/';
        } catch (Exception $e) {
            throw new Exception("Url has not been set");
        }

        if ($admin === TRUE) {
            $url .= 'admin/';
        }
        $url .= 'index.php/'.$page;

        header('Location: '.$url, TRUE);
        exit;
    }

    /**
     * Sets the url in the class.
     *
     * @param string $url The url to use.
     *
     * @return void
     *
     * @static
     */
    public static function setUrl($url)
    {
        self::$_url = $url;
    }

    /**
     * Gets the url from the class.
     *
     * @return string    Returns the url.
     * @throws exception Throws an exception if the url
     *                   hasn't been set before.
     *
     * @static
     */
    public static function getUrl()
    {
        if (self::$_url === NULL) {
            throw new Exception("Url has not been set");
        }

        return self::$_url;
    }
}

/* vim: set expandtab ts=4 sw=4: */
