<?php if (!defined('SK_PATH')) die ('No direct script access allowed');
/**
 * Skir
 *
 * An open source no-framework PHP mvc framework
 *
 * @package		Skir
 * @author		Manuele J Sarfatti
 * @copyright		Copyright (c) 2011, Manuele J Sarfatti
 * @license		http://creativecommons.org/licenses/by-sa/3.0/
 * @link		http://skirframe.com
 * @version		0.9
 * @date		7 may 2011
 */

// ------------------------------------------------------------------------

/**
 * System Core Classes
 * ===================
 *
 * This file loads core classes required for the framework to function.
 * Some classes have a shortcut alias (see Class Aliases section below).
 * ------------------------------------------------------------------------
 */


/**
 * Config Class
 *
 * Provides methods for working with configuration options and files.
 * Shortcut: 'c'
 */

class Config {

	private static $config = array();

	/**
	 * Getter
	 *
	 * @access		public
	 * @param string	the desired property
	 * @param string	an optional default value returned if the property is not found
	 * @return string
	 */
	public static function get($property = NULL, $default = NULL)
	{
		if (empty($property)) return self::$config;
		return isset(self::$config[$property]) ? self::$config[$property] : $default;
	}

	/**
	 * Setter
	 *
	 * @access		public
	 * @param string	the property name
	 * @param string	the value to be assigned to the property (optional if the property is an array)
	 * @return void
	 */
	public static function set($property, $value = NULL)
	{
		if (is_array($property))
		{
			self::$config = array_merge(self::$config, $property);
		}
		else
		{
			self::$config[$property] = $value;
		}
	}

	/**
	 * Loader
	 *
	 * @access		public
	 * @param string	the config file name
	 * @return array	the whole config array
	 */
	public static function load($file)
	{
		if (file_exists(SK_PATH.SYSTEM.'config/'.$file))
		{
			include_once SK_PATH.SYSTEM.'config/'.$file;
			self::set($config);
			return self::get();
		}
		else
		{
			trigger_error("Couldn't find the requested config file ({$file})");
		}
	}
}


/**
 * Loader Class
 *
 * Provides methods for loading resources.
 * Shortcut: 'l'
 */

class Load {

	/**
	 * Loader
	 *
	 * General file loader helper method
	 *
	 * @access		private
	 * @param string	the resource's location
	 * @return boolean
	 */
	private static function _file($resource, $type, $name)
	{
		if (file_exists($resource))
		{
			return include_once $resource;
		}
		else
		{
			trigger_error("Couldn't find the requested {$type} '{$name}' ({$resource})");
		}
	}

	/**
	 * Model Loader
	 *
	 * @access		public
	 * @param string	the real model name
	 * @param string	an optional custom model name
	 * @return boolean
	 */
	public static function model($model, $name = '')
	{
		if (self::_file(SK_PATH.APPLICATION.'models/'.$model.'.php', 'model', $model))
		{
			$name = $name ? $name : $model;

			$SK =& controller::get_instance();
			if (!isset($SK->$name))
			{
				return $SK->$name = new $model;
			}
			else
			{
				trigger_error("Couldn't load the model '{$model}' by the name '{$name}': name in use by another resource");
			}
		}
	}

	/**
	 * Library Loader
	 *
	 * @access		public
	 * @param string	the real library name
	 * @param string	an optional custom library name
	 * @return boolean
	 */
	public static function library($library, $name = '')
	{
		if (self::_file(SK_PATH.SYSTEM.'libraries/'.$library.'.class.php', 'library', $library))
		{
			$name = $name ? $name : $library;

			$SK =& controller::get_instance();
			if (!isset($SK->$name))
			{
				return $SK->$name = new $library;
			}
			else
			{
				trigger_error("Couldn't load the library '{$library}' by the name '{$name}': name in use by another resource");
			}
		}
	}

	/**
	 * Helper Loader
	 *
	 * @access		public
	 * @param string	the helper name
	 * @return boolean
	 */
	public static function helper($helper)
	{
		return (self::_file(SK_PATH.APPLICATION.'helpers/'.$helper.'.php', 'helper', $helper));
	}

	/**
	 * Language File Loader
	 *
	 * @access		public
	 * @param string	the file name
	 * @param string	the language corresponding folder
	 * @return boolean
	 */
	public static function lang($file, $language = 'english')
	{
		return (self::_file(SK_PATH.SYSTEM.'language/'.$language.'/'.$file.'.php', 'language file', $file));
	}

}


/**
 * View Class
 *
 * Loads a template file and provides a method for setting variables
 * Shortcut: 'v'
 */

class View {

	private static $_sk_data = array();

	/**
	 * View Loader
	 *
	 * @access		public
	 * @param string	the view name
	 * @param mixed		the optional parameters to pass to the view file
	 * @return void
	 */
	public static function render($view, $_sk_data = array())
	{
		$_sk_data = array_merge((array)$_sk_data, self::$_sk_data);
		extract($_sk_data, EXTR_OVERWRITE);
		self::$_sk_data = array(); // Don't propagate variables to successive includes
		$file = SK_PATH.APPLICATION.'views/'.$view.'.php';

		if (file_exists($file))
		{
			include $file;
		}
		else
		{
			trigger_error("Couldn't find the requested view '{$view}' ({$file})");
		}
	}

	/**
	 * Variable Setter
	 *
	 * @access		public
	 * @param string	the variable name
	 * @param string	the variable value
	 * @return void
	 */
	public static function set($name, $value)
	{
		self::$_sk_data[$name] = $value;
    }

	/**
	 * Error Handler
	 *
	 * @access		public
	 * @param string	the requested url
	 * @return void
	 */
	public static function error($code = '404')
	{
		//include SK_PATH.APPLICATION.'errors/'.$code.'.php';
		die("404 Page Not Found");
    }

}


/**
 * Database Driver
 *
 * Manages the PDO connection with the database and provides methods
 * for querying it
 */

class Database {

	private static $instance;
	private $dbh; // Holds the database connection
	private $sql;
	private $values;

	/**
	 * Constructor
	 *
	 * Connects to the database
	 *
	 * @access		private
	 * @param string	the database driver
	 * @param string	the database host
	 * @param string	the database name, or path
	 * @param string	the username to connect to the database
	 * @param string	the password to connect to the database
	 */
	private function __construct($driver, $host = '', $database, $username = '', $password = '') {

		switch ($driver)
		{
			case 'mysql':
				$this->dbh = new pdo("{$driver}:dbname={$database};host={$host}",
						     $username,
						     $password,
						     array(PDO::ATTR_PERSISTENT => TRUE));
				$this->dbh->exec('SET NAMES utf8');
				break;
			case 'sqlite':
				$this->dbh = new pdo("{$driver}:{$database}", array(PDO::ATTR_PERSISTENT => TRUE));
				break;
			default:
				trigger_error('DB Connection Failed. The driver type set in the configuration file wasn\'t recognized.',
					      E_USER_ERROR);
		}

		$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	}

	/**
	 * Singleton Pattern
	 *
	 * @access 		public
	 * @return object	instance of the Database class
	 */
	public static function &get_instance()
	{
		if (!self::$instance)
		{
			$db = c::get('db');
			self::$instance = new Database($db['driver'],
						       $db['host'],
						       $db['database'],
						       $db['username'],
						       $db['password']);
		}
		return self::$instance;
	}

	/**
	 * Execute Query
	 *
	 * Executes the supplied query with bindings
	 *
	 * @access		private
	 * @param string	the SQL query to be executed
	 * @param array		the values to bind to the query
	 * @return mixed	result set if SELECT, id if INSERT, TRUE if UPDATE, rows affected if other, FALSE on failure
	 */
	private function _do_query()
	{

		$stmt = $this->dbh->prepare($this->sql);

		if ($stmt->execute((array)$this->values))
		{
			if (preg_match('/^SELECT/is', $this->sql))
			{
				$result = $stmt->fetchAll(PDO::FETCH_OBJ);
				if (count($result, FALSE) == 1)
				{
					return $result[0];
				}
				else
				{
					return $result;
				}
			}
			elseif  (preg_match('/^INSERT/is', $this->sql))
			{
				return $this->dbh->lastInsertId();
			}
			else
			{
				return $stmt->rowCount();
			}
		}
		else
		{
			return FALSE;
		}

	}

	/**
	 * Raw Query
	 *
	 * Executes a raw query, possibly with bindings
	 *
	 * @access		public
	 * @param string	the SQL statement
	 * @param array		array of values to bind to placeholders
	 * @return mixed	depending on the query
	 */
	public function query($sql, $values = '')
	{
		$this->sql = $sql;
		$this->values = $values;

		return $this->_do_query();
	}

	/**
	 * Create / Update
	 *
	 * Builds an INSERT or UPDATE query, based on the optional $id param
	 *
	 * $this->db->set('table', array(column => value))	INSERT INTO $table ($column1, ...) VALUES ($value1, ...)
	 * $this->db->set('table', array(column => value), 10)	UPDATE $table SET $column1 = '$value1', ... WHERE id = 10
	 *
	 * @access		public
	 * @param string	the name of the table
	 * @param array		associative array with column => value pairs
	 * @param int		the id of the record to update
	 * @return mixed	the id on INSERT, number of affected rows on UPDATE, FALSE on failure
	 */
	public function set($table, $params, $id = NULL)
	{
		$id = intval($id);

		if ($id)
		{
			$this->sql = "UPDATE {$table} SET ";
			$columns = implode(' = ?, ', array_keys($params));
			$this->sql .= $columns . ' = ? ';
			$this->sql .= " WHERE id = {$id}";
			$this->values = array_values($params);
		}
		else
		{
			$this->sql = "INSERT INTO {$table} (";
			$columns = implode(', ', array_keys($params));
			$this->sql .= $columns . ') VALUES (';
			for ($i = count($params); $i > 0; $i--)
			{
				if ($i == 1)
				{
					$this->sql .= '?';
				}
				else
				{
					$this->sql .= '?, ';
				}
			}
			$this->sql .= ')';
			$this->values = array_values($params);
		}

		return $this->_do_query();
	}

	/**
	 * Read
	 *
	 * Builds a SELECT query, based on the arguments supplied
	 *
	 * $this->db->get('table')				SELECT * FROM $table
	 * $this->db->get('table', 10)				SELECT * FROM $table WHERE id = 10
	 * $this->db->get('table', array(columns))		SELECT column1, ... FROM $table
	 * $this->db->get('table', array(columns), 10)		SELECT column1, ... FROM $table WHERE id = 10
	 *
	 * @access		public
	 * @param string	the name of the table
	 * @param int/array	the requested id or an array of columns
	 * @param int		the requested id (if previous parameter is array)
	 * @return array	the result set as an array of objects
	 */
	public function get()
	{
		$this->values = '';
		$num_args = func_num_args();
		$table = func_get_arg(0);

		switch ($num_args)
		{
			case 1:
				$this->sql = "SELECT * FROM {$table}";
				break;
			case 2:
				if (is_numeric(func_get_arg(1)))
				{
					$id = intval(func_get_arg(1));
					$this->sql = "SELECT * FROM {$table} WHERE id = {$id}";
				}
				else
				{
					$columns = (array)func_get_arg(1);
					$columns = implode(', ', $columns);
					$this->sql = "SELECT {$columns} FROM {$table}";
				}
				break;
			case 3:
				$columns = (array)func_get_arg(1);
				$columns = implode(', ', $columns);
				$id = intval(func_get_arg(2));
				$this->sql = "SELECT {$columns} FROM {$table} WHERE id = {$id}";
				break;
			default:
				$this->sql = '';
				break;
		}

		return $this->_do_query();
	}

	/**
	 * Delete
	 *
	 * Deletes a record
	 *
	 * $this->db->delete('table', 10)	DELETE FROM $table WHERE id = 10
	 *
	 * @access		public
	 * @param string	the name of the table
	 * @param int/array	the id of the record to be deleted
	 * @return bool
	 */
	public function delete($table, $id)
	{
		$this->values = '';
		$id = intval($id);
		$this->sql = "DELETE FROM {$table} WHERE id = {$id}";

		return $this->_do_query();
	}

	/**
	 * Record exists?
	 *
	 * $this->db->exists('table', 10)	SELECT id FROM $table WHERE $param = $value
	 *
	 * @access		public
	 * @param string	the name of the table
	 * @param string	the name of the column
	 * @param string	the value we check against
	 * @return bool
	 */
	public function exists($table, $param, $value)
	{
		$this->sql = "SELECT id FROM {$table} WHERE {$param} = ?";
		$this->values = $value;

		return (bool)$this->_do_query();
	}

	/**
	 * Get last query
	 *
	 * @access		public
	 * @return string	last executed querying
	 */
	public function last_query()
	{
		return $this->sql;
	}

	/**
	 * Get last bindings values
	 *
	 * @access		public
	 * @return string	last bindings values separated by ','
	 */
	public function last_bindings()
	{
		return implode(', ', (array)$this->values);
	}

}


/**
 * Skir Controller Class
 *
 * This abstract class is the one every application controller has to be derived from.
 */

abstract class Controller {

	private static $instance;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		self::$instance =& $this;
	}

	public static function &get_instance()
	{
		return self::$instance;
	}

	// All controllers must contain an index method
	abstract function index();

}


/**
 * Skir Model Class
 *
 * Creates the database object that every derived model will use.
 */

class Model {

	protected $db; // Holds the database connection

	public function __construct()
	{
		$this->db =& database::get_instance();
	}

}


/*
 |-------------------------------------------------------------------------
 |  Class aliases (for php < 5.3 a substitute function is provided)
 |-------------------------------------------------------------------------
*/

if (!function_exists('class_alias'))
{
    function class_alias($original, $alias)
    {
        eval('class ' . $alias . ' extends ' . $original . ' {}');
    }
}

class_alias('Config', 'c');
class_alias('Load', 'l');
class_alias('View', 'v');

/* End of file core.php
  Location: ./system/core/core.php */