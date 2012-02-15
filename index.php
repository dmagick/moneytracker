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

require $basedir.'/config/config.php';

/**
 * A list of systems.
 * All of these are included at the start.
 * Everything is made available.
 * This list is also used to by isValidSystem
 * to make sure a user isn't trying to cause errors by
 * making up their own url.
 * 
 * @see isValidSystem
 */
$systems = array(
	'db',
	'frontend',
	'template',
	'url',
    'account',
    'account_transaction',
    'session',
    'messagelog',
    'user',
);

/**
 * Helper function to make sure the requested system is valid.
 * Just in case someone decides to change the url (hoping for
 * information disclosure etc).
 *
 * @param string $systemName The system being checked
 *
 * @uses systems
 *
 * @return boolean
 */
function isValidSystem($systemName=NULL)
{
    global $systems;
    if (in_array($systemName, $systems) === TRUE) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Include all of our required systems.
 * Since we're using a consistent structure,
 * we can just loop over 'em to do it all in one go.
 */
foreach ($systems as $system) {
	require $basedir.'/systems/'.$system.'/'.$system.'.php';
}

session::setDir($config['cachedir']);
session::start();

messagelog::setLog($config['cachedir'].'/debug.log');

db::connect($config['db']);

url::setUrl($config['url']);

template::setDir($basedir.'/templates');

frontend::display();

/* vim: set expandtab ts=4 sw=4: */
