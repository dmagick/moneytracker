<?php
/**
 * Session class file.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

/**
 * The session class.
 * Handles setting, loading, checking, removing and
 * dealing with flash messages.
 *
 * @package money
 */
class session
{
    /**
     * Base session dir.
     */
    private static $_sessionDir = NULL;

    /**
     * Flash messages are put in here.
     * They get set by one page and displayed on another (then
     * after being displayed they are removed).
     */
    private static $_flashMessages = array();

    /**
     * Set the directory where to get session files from.
     * This is done at the top of the main index script.
     * Does a basic check to make sure the dir exists and is
     * writable, and if it doesn't exist or isn't writable
     * it will throw an exception.
     *
     * @param string $dir The session dir to use.
     *
     * @see _sessionDir
     *
     * @return void
     */
    public static function setDir($dir)
    {
        if (is_dir($dir) === FALSE) {
            throw new Exception("Session dir doesn't exist");
        }

        if (is_writable($dir) === FALSE) {
            throw new Exception("Session dir is not writable");
        }

        self::$_sessionDir = $dir;
    }

    /**
     * Start the session.
     * Will throw an exception if the session dir hasn't been set.
     *
     * @return void
     */
    public static function start()
    {
        if (self::$_sessionDir === NULL) {
            throw new Exception("Session dir hasn't been set");
        }
        session_save_path(self::$_sessionDir);
        session_start();

        if (isset($_SESSION['_flashMessages']) === TRUE) {
            self::$_flashMessages = $_SESSION['_flashMessages'];
        }
    }

    /**
     * Set a session var to a particular value.
     *
     * @param string $var The variable to put in the session.
     * @param mixed  $val The value to set the variable to.
     *
     * @return void
     */
    public static function set($var, $val)
    {
        $_SESSION[$var] = $val;
    }

    /**
     * Get a variable's value from the session.
     *
     * @param string $var The var to get back from the session.
     *
     * @return mixed Returns the value of the variable in the session.
     * @throws exception Throws an exception when the variable isn't set.
     */
    public static function get($var)
    {
        if (isset($_SESSION[$var]) === FALSE) {
            throw new Exception("$var has not been set in the session");
        }

        return $_SESSION[$var];
    }

    /**
     * Check if a variable is set in the session.
     *
     * @param string $var The var to check is in the session.
     *
     * @return boolean Returns true if the variable is in the session, false if
     *                 it's not available.
     */
    public static function has($var)
    {
        if (isset($_SESSION[$var]) === TRUE) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Remove a variable from the session.
     *
     * @param string $var The var to remove from the session.
     *
     * @return boolean Returns true if the variable is removed.
     * @throws exception Throws an exception when the variable isn't set.
     */
    public static function remove($var)
    {
        if (self::has($var) === FALSE) {
            throw new Exception("$var has not been set in the session");
        }
        unset($_SESSION[$var]);
        return TRUE;
    }

    /**
     * Add a flash message to the queue.
     *
     * These are messages that get displayed once then discarded.
     * eg 'You don't have permissions to do X'.
     * Once the attempt has been made, there is no reason to keep
     * the message around.
     *
     * @param string $message The message to keep note of.
     * @param string $type    The message type (success/failure).
     *
     * @return void
     *
     * @uses session::set
     * @see  session::_flashMessages
     */
    public static function setFlashMessage($message='', $type='')
    {
        self::$_flashMessages[] = array($message, $type);
        self::set('_flashMessages', self::$_flashMessages);
    }

    /**
     * Get all available flash messages to display in a template.
     *
     * @return array Returns an array of flash messages (per flash
     *               message - the content, and it's type).
     *               If no messages are available, it'll be an empty
     *               array.
     */
    public static function getFlashMessages()
    {
        $messages = array();
        try {
            $messages = self::get('_flashMessages');
            self::$_flashMessages = array();
            self::remove('_flashMessages');
        } catch (Exception $e) {
        }
        return $messages;
    }

    /**
     * Save the session. 
     *
     * This is only needed if you want to close permanently and not
     * allow any more variables (or flash messages) to be saved.
     *
     * @return void
     */
    public static function save()
    {
        session_write_close();
    }
}

/* vim: set expandtab ts=4 sw=4: */
