<?php if (!defined('SK_PATH')) die ('No direct script access allowed');
/**
 * Skir
 *
 * An open source no-framework PHP mvc framework
 *
 * @package			Skir
 * @author			Manuele J Sarfatti
 * @copyright		Copyright (c) 2011, Manuele J Sarfatti
 * @license			http://creativecommons.org/licenses/by-sa/3.0/
 * @link			http://skirframe.com
 * @version			0.9.1
 * @date			15 nov 2011
 */

// ------------------------------------------------------------------------

/**
 * System Toolkit
 * ==============
 *
 * This file contains an extended set of helper functions and classes of
 * general use.
 * ------------------------------------------------------------------------
 */


/*
 |-------------------------------------------------------------------------
 |  Constants
 |-------------------------------------------------------------------------
*/

define('SECOND', 1);
define('MINUTE', 60 * SECOND);
define('HOUR', 60 * MINUTE);
define('DAY', 24 * HOUR);
define('MONTH', 30 * DAY);
define('YEAR', 365 * DAY);


/*
 |-------------------------------------------------------------------------
 |  Helpers
 |-------------------------------------------------------------------------
*/

// Dumps variables in a preformatted box. Can optionally kill the script.
function dump($item, $die = FALSE)
{
    $print_string = '<pre>' . print_r($item, TRUE) . '</pre>';
    if($die)
        die($print_string);
    else
        echo $print_string;
}

// Friendly dates: $time is a GM-based Unix timestamp, this makes for a timezone neutral comparison
function relative_time($time)
{
	$delta = strtotime(gmdate("Y-m-d H:i:s", time())) - $time;

	if ($delta < 1 * MINUTE)
	{
		return "moments ago";
	}
	if ($delta < 2 * MINUTE)
	{
		return "a minute ago";
	}
	if ($delta < 45 * MINUTE)
	{
		return floor($delta / MINUTE) . " minutes ago";
	}
	if ($delta < 90 * MINUTE)
	{
		return "an hour ago";
	}
	if ($delta < 24 * HOUR)
	{
		return floor($delta / HOUR) . " hours ago";
	}
	if ($delta < 48 * HOUR)
	{
		return "yesterday";
	}
	if ($delta < 30 * DAY)
	{
		return floor($delta / DAY) . " days ago";
	}
	if ($delta < 12 * MONTH)
	{
		$months = floor($delta / MONTH);
		return $months <= 1 ? "one month ago" : $months . " months ago";
	}
	else
	{
		$years = floor($delta / YEAR);
		return $years <= 1 ? "one year ago" : $years . " years ago";
	}
}

// Does a "header redirect" to the URI specified.
function go($url = '/', $code = '301')
{
	// send an appropriate header
	switch($code)
	{
		case 301:
			header('HTTP/1.1 301 Moved Permanently');
			break;
		case 302:
			header('HTTP/1.1 302 Found');
			break;
		case 303:
			header('HTTP/1.1 303 See Other');
			break;
	}

	// send to new page
	header('Location:' . $url);
	exit;
}


/*
 |-------------------------------------------------------------------------
 |  Classes
 |-------------------------------------------------------------------------
*/


/**
 * Cookie Class
 *
 * This class helps to handle browser cookies
 * Shortcut: 'ck'
 */

class Cookie {

	/**
	 * Set a cookie
	 *
	 * @access			public
	 * @param string	the cookie name
	 * @param string	the value (if it is an array it will be converted to a json string)
	 * @param number	the seconds you want the cookie to live from this moment
	 * @param string	the path on the server in which the cookie will be available on
	 * @param string	the domain that the cookie is available to
	 * @param bool		whether the cookie should only be transmitted over a secure HTTPS connection from the client
	 * @return void
	 */
	public static function set($key, $value, $expire = 3600, $path = '/', $domain = '', $secure = FALSE)
	{
		if (is_array($value))
		{
			$value = json_encode($value);
		}
		$_COOKIE[$key] = $value;
		setcookie($key, $value, time() + $expire, $path, $domain, $secure);
	}

	/**
	 * Get a cookie
	 *
	 * @access			public
	 * @param string	the cookie name
	 * @param bool		whether the value should be treated as a json string and decoded to an assoc array
	 * @return void
	 */
	public static function get($field, $json = FALSE)
	{
		$value = (isset($_COOKIE[$field])) ? $_COOKIE[$field] : false;
		$value = $json ? json_decode($value, TRUE) : $value;
		return $value;
	}

	/**
	 * Delete a cookie
	 *
	 * @access			public
	 * @param string	the cookie name
	 * @param string	the path (as set on cookie set)
	 * @param string	the domain (as set on cookie set)
	 * @param bool		safe connection (as set on cookie set)
	 * @return void
	 */
	public static function delete($key, $path = '/', $domain = '', $secure = FALSE)
	{
		setcookie($key, false, time() - 3600, $path, $domain, $secure);
		unset($_COOKIE[$key]);
	}

}


/**
 * Form Class
 *
 * This class helps to handle form submission and validation
 * Shortcut: 'f'
 */

class Form {
		
	private static $_errors = array(); // Holds every error found
	private static $_has_errors = array(); // Holds the fields with errors

	/**
	 * Validate an input field
	 *
	 * @access			public
	 * @param string	the name of the field to be validated
	 * @param string	the rule(s) to be applied
	 * @param string	a human readable name for the field (to be used when showing errors)
	 * @return bool
	 */
	public static function validate($field, $rules = '', $label = '')
	{
		// If no arguments are passed, see if there where any errors or return TRUE
		if (empty($field)) return (!empty(self::$_errors)) ? FALSE : TRUE;
		
		// If no rule is passed, see if there where any errors in the specified field or return TRUE
		if (empty($rules)) return (!empty(self::$_has_errors) && isset(self::$_has_errors[$field])) ? FALSE : TRUE;

		// If the field label wasn't passed we use the field name
		$label = ($label == '') ? $field : $label;
		
		// Multiple rules can be indicated (e.g. 'required|max_length[16]|unique')
		$rules = explode("|", $rules);
		
		$str = i::post($field);
			
		foreach ($rules as $rule) {
		
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}
			
			switch ($rule) {
			
				case 'required':					
					if (!is_array($str))
					{
						$result = (trim($str) == '') ? FALSE : TRUE;
					}
					else
					{
						$result = (!empty($str));
					}
					break;
					
				case 'max_length':
					if (preg_match("/[^0-9]/", $param))
					{
						$result = FALSE;
						break 2;
					}
					$result = (strlen($str) > $param) ? FALSE : TRUE;
					break;
					
				case 'min_length':
					if (preg_match("/[^0-9]/", $param))
					{
						$result = FALSE;
						break 2;
					}
					$result = (strlen($str) < $param) ? FALSE : TRUE;
					break;
					
				case 'alpha':
					$result = (!preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
					break;
					
				case 'alpha_num':
					$result = (!preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
					break;
					
				case 'alpha_dash':
					$result = (!preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
					break;
					
				case 'numeric':
					$result = (!preg_match("/^[\-+]?[0-9]*(\.|,)?[0-9]+$/", $str)) ? FALSE : TRUE;
					break;
					
				case 'range':
					list($min, $max) = explode('-', $param);
					if ($min === '')
					{
						$result = ($str >= $max) ? FALSE : TRUE;
					}
					else if ($max === '')
					{
						$result = ($str <= $min) ? FALSE : TRUE;
					}
					else
					{
						$result = ($str <= $min OR $str >= $max) ? FALSE : TRUE;
					}
					break;
					
				case 'regex':
					$result = (!preg_match($param, $str)) ? FALSE : TRUE;
					break;
					
				case 'matches':
					if (!isset($_POST[$param]))
					{
						$result = FALSE;
					}			
					$param = $_POST[$param];			
					$result = ($str !== $param) ? FALSE : TRUE;
					break;
					
				case 'unique':
					list($table, $param) = explode('.', $param);
					$db =& database::get_instance();
					$result = $db->exists($table, $param, $str);
					break;
					
				case 'date':
					$time = strtotime($str);
					if(!$time) $result = FALSE;			
					$year	= date('Y', $time);
					$month = date('m', $time);
					$day	 = date('d', $time);			
					$result = (!checkdate($month, $day, $year)) ? FALSE : $time;
					break;
					
				case 'email':
					$result = (!filter_var($str, FILTER_VALIDATE_EMAIL)) ? FALSE : TRUE;
					break;
					
				case 'url':
					$result = (!filter_var($str, FILTER_VALIDATE_URL)) ? FALSE : TRUE;
					break;
					
				default:
					$result = $rule($str);
					
			}
			
			if ($result === FALSE)
			{
				self::$_has_errors[$field] = TRUE;
				self::$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
			}
			
			// If the field was required but is empty, skip successive checks
			if ($rule == 'required' && $result === FALSE) break;
			
			$str = (is_bool($result)) ? $str : $result;
		}
		
		return $str;
	}
	
	/**
	 * Show errors from validation
	 *
	 * @access			public
	 * @param string	the HTML delimiter between error messages
	 * @param string	the language code
	 * @param string	the (optional) field name
	 * @return bool
	 */	
	public static function show_errors($field = '')
	{
		// Do nothing if there are no errors
		if (empty(self::$_errors)) return FALSE;
		
		include SK_PATH.SYSTEM.'language/'.c::get('language').'/form_validation.lang.php';
		
		$loop = FALSE;
		
		foreach (self::$_errors as $error)
		{
			if (empty($field) OR $error['field'] == $field)
			{
				if ($loop) echo c::get('error_delimiter') . "\n";				
				printf($error_msg[$error['rule']], $error['label'], $error['param']);												
				$loop = TRUE;
			}
		}
	}

}


/**
 * Input Class
 *
 * This class helps to handle input data
 * Shortcut: 'i'
 */

class Input {

	/**
	 * Easy access to HTML form data
	 *
	 * @access			public
	 * @param string	the field name
	 * @return mixed
	 */

	// Post
	// ---------------------------------------------------------------------------
	public static function post($field)
	{
		return (isset($_POST[$field])) ? $_POST[$field] : false;
	}

	// Get
	// ---------------------------------------------------------------------------
	public static function get($field)
	{
		return (isset($_GET[$field])) ? $_GET[$field] : false;
	}

	// Files
	// ---------------------------------------------------------------------------
	public static function files($field)
	{
		return (isset($_FILES[$field])) ? $_FILES[$field] : false;
	}

	// Request
	// ---------------------------------------------------------------------------
	public static function request($field)
	{
		return (isset($_REQUEST[$field])) ? $_REQUEST[$field] : false;
	}
}


/**
 * Output Class
 *
 * This class helps to handle content output
 * Shortcut: 'o'
 */

class Output {

	/**
	 * Start object buffering
	 *
	 * @access			public
	 * @return void
	 */
	public static function start() {
		ob_start();
	}

	/**
	 * End object buffering and optionally return the content without flushing it
	 *
	 * @access			public
	 * @param bool		do we want to return the content?
	 * @return mixed
	 */
	public static function end($return = FALSE) {
		if ($return)
		{
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		ob_end_flush();
	}

	/**
	 * Set the content type and charset of the resource
	 *
	 * @access			public
	 * @param string	a shortcut for the content type or a mime type
	 * @param string	the charset
	 * @return void
	 */
	public static function type($content_type = 'text', $charset = 'utf-8')
	{
		// shortcuts for content types
		$mime_types = array(
			'text'	=> 'text/plain',
			'html'	=> 'text/html',
			'xml'	=> 'text/xml',
			'css'	=> 'text/css',
			'js'	=> 'text/javascript',
			'json'	=> 'application/json',
			'jpg'	=> 'image/jpeg',
			'png'	=> 'image/png',
			'gif'	=> 'image/gif'
		);

		$content_type = array_key_exists($content_type, $mime_types)
			      ? $mime_types[$content_type]
			      : $content_type;

		header('Content-type: ' . $content_type . '; ' . $charset);
	}

}


/*
 |-------------------------------------------------------------------------
 |  Class aliases
 |-------------------------------------------------------------------------
*/

class_alias('Cookie', 'ck');
class_alias('Form', 'f');
class_alias('Input', 'i');
class_alias('Output', 'o');


/* End of file toolkit.php
  Location: ./system/core/toolkit.php */