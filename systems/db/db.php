<?php

class db
{

	/**
	 * Keep the db connection handle.
	 */
	private static $_dbconn = NULL;

	private static $_tablePrefix = '';

	/**
	 * Try to connect to the db based on the details passed in.
	 * The details contain:
	 * type (pgsql, mysql etc)
	 * dbname
	 * username
	 * password
	 *
	 * Throws an exception if a connection can't be established.
	 *
	 * @return TRUE
	 */
	public static function connect(array $details)
	{
		$required = array(
				'dbname',
				'type',
				'username',
				);

		$connstring = '';
		foreach ($required as $reqField) {
			if (isset($details[$reqField]) === FALSE) {
				throw new Exception("Required field ".$reqField." is missing");
			}
			if (empty($details[$reqField]) === TRUE) {
				throw new Exception("Required field ".$reqField." is empty");
			}
		}

		$connstring .= $details['type'].':dbname='.$details['dbname'].';';
		if (isset($details['host']) === TRUE && empty($details['host']) === FALSE) {
			$connstring .= 'host='.$details['host'].';';
		}
		if (isset($details['port']) === TRUE && empty($details['port']) === FALSE) {
			if ($details['port'] > 0) {
				$connstring .= 'port='.$details['port'].';';
			}
		}

		try {
			if (empty($details['password']) === FALSE) {
				$dbconn = new PDO($connstring, $details['username'], $details['password']);
			} else {
				$dbconn = new PDO($connstring, $details['username']);
			}
		} catch (PDOException $e) {
			throw new Exception("Unable to connect to db: ".$e->getMessage());
		}
		self::$_dbconn = $dbconn;

		self::$_tablePrefix = $details['prefix'];

		return TRUE;

	}

	public static function getPrefix()
	{
		return self::$_tablePrefix;
	}

    public static function beginTransaction()
    {
        messagelog::logmessage("BEGIN;");
        $result = self::$_dbconn->beginTransaction();
        return $result;
    }

    public static function commitTransaction()
    {
        messagelog::logmessage("COMMIT;");
        $result = self::$_dbconn->commit();
        return $result;
    }

    public static function rollbackTransaction()
    {
        messagelog::logmessage("ROLLBACK;");
        $result = self::$_dbconn->rollback();
        return $result;
    }

	/**
	 * Execute sql passed in.
	 * You write your own sql and pass it through here,
	 * along with any values to pass in to the sql.
	 * This runs the query and returns whether it worked or not.
	 *
	 * @param string $sql The sql to run.
	 * @param array $values Bind-values to pass in to the sql
	 *
	 * @return boolean
	 */
	public static function execute($sql, array $values=array())
	{
        messagelog::logmessage($sql);
        messagelog::logmessage($values);
		$query = self::$_dbconn->prepare($sql);
		if (empty($values) === TRUE) {
			$result = $query->execute();
		} else {
			$result = $query->execute($values);
		}
		return $result;
	}

	/**
	 * select based on the sql passed in.
	 * You write your own sql and pass it through here,
	 * along with any values to pass in to the sql.
	 * This runs the query and returns a statement handler for use
	 * with the fetch() method.
	 *
	 * @param string $sql The sql to run.
	 * @param array $values Bind-values to pass in to the sql
	 *
	 * @return object
	 */
	public static function select($sql, array $values=array())
	{
        messagelog::logmessage($sql);
        messagelog::logmessage($values);
		$query = self::$_dbconn->prepare($sql);
		if (empty($values) === TRUE) {
			$query->execute();
		} else {
			$query->execute($values);
		}
		return $query;
	}

	/**
	 * Fetch the next row from a select query.
	 * Simply a wrapper for PDOStatement::fetch
	 * Sets the row to be returned as an associative array.
	 *
	 * @param object $queryObject Query object from a previous select() call.
	 *
	 * @return mixed
	 */
	public static function fetch($queryObject)
	{
		return $queryObject->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Fetch all rows from a select query.
	 * Simply a wrapper for PDOStatement::fetchAll
	 * Sets the rows to be returned as an associative array.
	 *
	 * @param object $queryObject Query object from a previous select() call.
	 *
	 * @return mixed
	 */
	public static function fetchAll($queryObject)
	{
		return $queryObject->fetchAll(PDO::FETCH_ASSOC);
	}

}

/* vim: set expandtab ts=4 sw=4: */
