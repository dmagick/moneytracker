<?php

class url
{
	private static $_url = NULL;

	/**
	 * Redirect to another page with a header redirect.
	 *
	 * @param string $page The page to redirect to.
	 * @param boolean $admin Whether it's in the admin area or not.
	 *
	 * @return void
	 */
	public static function redirect($page, $admin=FALSE)
	{
		$url = self::getUrl().'/';
		if ($admin === TRUE) {
			$url .= 'admin/';
		}
		$url .= 'index.php/'.$page;

		header('Location: '.$url, TRUE);
		exit;
	}

	public static function setUrl($url)
	{
		self::$_url = $url;
	}

	public static function getUrl()
	{
		if (self::$_url === NULL) {
			throw new Exception("Url has not been set");
		}

		return self::$_url;
	}
}
