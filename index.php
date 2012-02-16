<?php
/**
 * Index file handles the start up stuff.
 * Its all then handed off to the appropriate
 * system for it to deal with the rest.
 *
 * @author Chris Smith <dmagick@gmail.com>
 * @version 1.0
 * @package money
 */

ini_set('display_errors', true);
error_reporting(E_ALL);

/**
* Work out where we are.
* We can use full paths instead of relative paths
* to save searching the include_path etc.
*/
$basedir = dirname(__FILE__);

/**
* We don't use any external scripts so keep the path
* as simple as possible.
*/
ini_set('include_path', $basedir.':');

require $basedir.'/systems/init.php';

frontend::display();

/* vim: set expandtab ts=4 sw=4: */
