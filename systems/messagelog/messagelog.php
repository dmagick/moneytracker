<?php

class MessageLog
{
	private static $_logFile = NULL;
    private static $_enabled = FALSE;

    public static function enable()
    {
        self::$_enabled = TRUE;
    }

    public static function disable()
    {
        self::$_enabled = FALSE;
    }

	public static function setLog($logFile)
	{
		if (file_exists($logFile) === TRUE) {
			if (is_writable($logFile) === TRUE) {
				self::$_logFile = $logFile;
				return;
			}
			throw new Exception("Unable to set log file - it exists but is not writable");
		}
        $parent = dirname($logFile);
        if (is_dir($parent) === TRUE) {
            if (is_writable($parent) === TRUE) {
				self::$_logFile = $logFile;
				return;
            }
			throw new Exception("Unable to set log file - parent directory exists but is not writable");
        }
        if (mkdir($parent, 0755, TRUE) === TRUE) {
            self::$_logFile = $logFile;
            return;
        }
        throw new Exception("Unable to set log file - unable to make directory");
	}

	public static function LogMessage($info)
	{
        if (self::$_enabled === FALSE) {
            return;
        }

		if (self::$_logFile === NULL) {
			throw new Exception("Log file has not been set");
		}
        $type = gettype($info);
        switch ($type) {
            case 'boolean':
                $info = var_export($info, TRUE);
                
            case 'double':
            case 'integer':
            case 'string':
                $info = trim($info);
            break;

            case 'array':
            case 'object':
                $info = print_r($info, TRUE);
            break;

            default:
                throw new Exception("Not sure how to handle this type of variable: ".gettype($info));
        }
        error_log($info."\n", 3, self::$_logFile);
	}
}

/* vim: set expandtab ts=4 sw=4: */
