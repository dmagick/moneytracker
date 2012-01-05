<?php

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
