<?php

class session
{
	/**
	 * Base session dir.
	 */
	private static $_sessionDir = NULL;

	private static $_flashMessages = array();

	/**
	 * Set the directory where to get session files from.
	 * This is generally done at the top of the main index script.
	 * Does a basic check to make sure the dir exists, and if
	 * it doesn't it will throw an exception.
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

	public static function set($var, $val)
	{
		$_SESSION[$var] = $val;
	}

	public static function get($var)
	{
		if (isset($_SESSION[$var]) === FALSE) {
			throw new Exception("$var has not been set in the session");
		}

		return $_SESSION[$var];
	}

	public static function has($var)
	{
		if (isset($_SESSION[$var]) === TRUE) {
			return TRUE;
		}
		return FALSE;
	}

	public static function remove($var)
	{
		if (self::has($var) === FALSE) {
			throw new Exception("$var has not been set in the session");
		}
		unset($_SESSION[$var]);
		return TRUE;
	}

	public static function setFlashMessage($message='', $type='')
	{
		self::$_flashMessages[] = array($message, $type);
        self::set('_flashMessages', self::$_flashMessages);
	}

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

	public static function save()
	{
		session_write_close();
	}
}

/* vim: set expandtab ts=4 sw=4: */
