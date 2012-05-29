<?php

require dirname(dirname(__FILE__)).'/config/config.php';

/**
 * If it's not a cli request, redirect back to the main url.
 */
if (strpos('cli', php_sapi_name()) === FALSE) {
    header('Location: '.$config['url']);
    exit;
}

if (defined('PHPUnit_MAIN_METHOD') === FALSE) {
    define('PHPUnit_MAIN_METHOD', 'MoneyTests_AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Framework/TestSuite.php';

class MoneyTests_AllTests
{

    /**
     * Prepare the test runner.
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());

    }//end main()


    /**
     * Add all unit tests into a test suite.
     *
     * Unit tests are found by recursing through the 'Tests' directories
     * throughout the source.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Money Tests');

        $basedir = dirname(dirname(__FILE__)).'/systems';
        $allTests = self::_getTests($basedir);
        foreach ($allTests as $testFile) {
            require $testFile;
            $classname = basename($testFile, '.test').'Test';
            $suite->addTestSuite($classname);
        }
        return $suite;

    }//end suite()

    private static function _getTests($dir)
    {
        $tests = array();
        $d     = dir($dir);
        while (FALSE !== ($entry = $d->read())) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if ($entry === 'jpgraph') {
                continue;
            }

            $fullpath = $dir.'/'.$entry;
            if (is_dir($fullpath) === TRUE) {
                $foundTests = self::_getTests($fullpath);
                $tests      = array_merge($tests, $foundTests);
                continue;
            }
            $info = pathinfo($fullpath);
            if (isset($info['extension']) === TRUE && $info['extension'] === 'test') {
                $tests[] = $fullpath;
            }
        }
        $d->close();
        return $tests;
    }

}

/* vim: set expandtab ts=4 sw=4: */

