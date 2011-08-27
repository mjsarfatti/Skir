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
function redirect($url = '/', $code = '301')
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
	 * @access		public
	 * @param string	the cookie name
	 * @param string	the value (if it is an array it will be converted to a json string)
	 * @param number	the seconds you want the cookie to live from this moment
	 * @param string	the path on the server in which the cookie will be available on
	 * @param string	the domain that the cookie is available to
	 * @param bool		whether the cookie should only be transmitted over a secure HTTPS connection from the client
	 * @return void
	 */
	function set($name, $value, $expire = 3600, $path = '/', $domain = '', $secure = FALSE)
	{
		if (is_array($value))
		{
			$value = json_encode($value);
		}
		$_COOKIE[$name] = $value;
		setcookie($name, $value, time() + $expire, $path, $domain, $secure);
	}

	/**
	 * Get a cookie
	 *
	 * @access		public
	 * @param string	the cookie name
	 * @param bool		whether the value should be treated as a json string and decoded to an assoc array
	 * @return void
	 */
	function get($name, $json = FALSE)
	{
		$value = $json ? json_decode($_COOKIE[$name], TRUE) : $_COOKIE[$name];
		return $value;
	}

	/**
	 * Delete a cookie
	 *
	 * @access		public
	 * @param string	the cookie name
	 * @param string	the path (as set on cookie set)
	 * @param string	the domain (as set on cookie set)
	 * @param bool		safe connection (as set on cookie set)
	 * @return void
	 */
	function delete($name, $path = '/', $domain = '', $secure = FALSE)
	{
		setcookie($name, false, time() - 3600, $path, $domain, $secure);
		unset($_COOKIE[$name]);
	}

}


/**
 * Form Class
 *
 * This class helps to handle form submission and validation
 * Shortcut: 'f'
 */

class Form {
		
	// Initializes the 'error' array
	private static $_errors = array();

	/**
	 * Validate an input field
	 *
	 * @access		public
	 * @param string	the name of the field to be validated
	 * @param string	a human readable name for the field (to be used when showing errors)
	 * @param string	the rule(s) to be applied
	 * @return bool
	 */
	public static function validate($field, $label = '', $rules = '')
	{
		// If the field label wasn't passed we use the field name
		$label = ($label == '') ? $field : $label;
		
		$rules = explode("|", $rules);
		
		$str = $_POST[$field];
			
		foreach ($rules as $rule) {
		
			// Strip the parameter (if exists) from the rule
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
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'max_length':
					if (preg_match("/[^0-9]/", $param))
					{
						$result = TRUE;
					}
					$result = (strlen($str) > $param) ? FALSE : TRUE;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'min_length':
					if (preg_match("/[^0-9]/", $param))
					{
						$result = TRUE;
					}
					$result = (strlen($str) < $param) ? FALSE : TRUE;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'regex':
					$result = (!preg_match($param, $str)) ? FALSE : TRUE;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'matches':
					if (!isset($_POST[$param]))
					{
						$result = FALSE;
					}			
					$param = $_POST[$param];			
					$result = ($str !== $param) ? FALSE : TRUE;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'unique':
					list($table, $param)=explode('.', $param);
					$db =& database::get_instance();
					$result = $db->exists($table, $param, $str);
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'date':
					$time = strtotime($str);
					if(!$time) $result = FALSE;			
					$year	= date('Y', $time);
					$month = date('m', $time);
					$day	 = date('d', $time);			
					$result = (!checkdate($month, $day, $year)) ? FALSE : $time;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'email':
					$result = (!filter_var($str, FILTER_VALIDATE_EMAIL)) ? FALSE : TRUE;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				case 'url':
					$result = (!filter_var($str, FILTER_VALIDATE_URL)) ? FALSE : TRUE;
					if (!$result) self:$_errors[] = array('field' => $field, 'rule' => $rule, 'label' => $label, 'param' => $param);
					break;
					
				default:
					$result = $rule($str);
					
				// TODO: Add 'number', 'int', 'Name', 'username', 'password', 'range'
			}
			
			$str = (is_bool($result)) ? $str : $result;
		}
		
		return (empty(self::$_errors)) ? $str : FALSE;
	}
	
	/**
	 * Show errors from validation
	 *
	 * @access		public
	 * @param string	the HTML delimiter between error messages
	 * @param string	the language code
	 * @param string	the (optional) field name
	 * @return bool
	 */	
	public static function show_errors($delimiter = '<br />', $lang = 'english', $field = '')
	{
		// Do nothing if there are no errors
		if (empty(self::$_errors)) return;
		
		//l::lang('form_validation', $lang);
		
		$loop = FALSE;
		
		foreach (self::$_errors as $error)
		{
			if (empty($field) OR $error['field'] == $field)
			{
				if ($loop) echo $delimiter;
				
				switch ($error['rule'])
				{
					case 'required':					
						echo "The $error['label'] field is required.";
						// printf($error_msg['required'], $error['label']);
						break;
						
					case 'max_length':
						echo "The $error['label'] field cannot exceed $error['param'] characters in length.";
						break;
						
					case 'min_length':
						echo "The $error['label'] field must be at least $error['param'] characters in length.";
						break;
						
					case 'regex':
						echo "The $error['label'] field is not in the correct format.";
						break;
						
					case 'matches':
						echo "The $error['label'] field does not match the $error['param'] field.";
						break;
						
					case 'unique':
						echo "The $error['label'] field must contain a unique value.";
						break;
						
					case 'date':
						echo "The $error['label'] field is not a valid date.";
						break;
						
					case 'email':
						echo "The $error['label'] field is not a valid email address.";
						break;
						
					case 'url':
						echo "The $error['label'] field is not a valid URL";
						break;
						
					default:
						echo "The $error['label'] field is not valid.";
				}
				
				$loop = TRUE;
			}
		}
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
	 * @access		public
	 * @return void
	 */
	function start() {
		ob_start();
	}

	/**
	 * End object buffering and optionally return the content without flushing it
	 *
	 * @access		public
	 * @param bool		do we want to return the content?
	 * @return mixed
	 */
	function end($return = FALSE) {
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
	 * @access		public
	 * @param string	a shortcut for the content type or a mime type
	 * @param string	the charset
	 * @return void
	 */
	function type($content_type = 'text', $charset = 'utf-8')
	{
		// shortcuts for content types
		$mime_types = array(
			'text'	=> 'text/plain',
			'html'	=> 'text/html',
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
class_alias('Output', 'o');


/* End of file toolkit.php
  Location: ./system/core/toolkit.php */