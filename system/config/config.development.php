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
 * System Configuration Settings
 *
 * Holds configuration values to be used in a certain environment.
 */

/*
 |-------------------------------------------------------------------------
 |  System
 |-------------------------------------------------------------------------
*/
$config['default_controller'] = 'welcome';
$config['autoload']['library'] = array('');
$config['autoload']['model'] = array('');
$config['autoload']['helper'] = array('');
$config['error_delimiter'] = '<br />';		// Or </p><p>, etc.

/*
 |-------------------------------------------------------------------------
 |  Database
 |-------------------------------------------------------------------------
*/
$config['db']['driver'] = 'mysql'; 		// Supported drivers: mysql|sqlite
$config['db']['host'] = 'localhost';
$config['db']['database'] = 'skir';		// Absolute path to database file if using 'sqlite'
$config['db']['username'] = 'root';
$config['db']['password'] = 'root';

/*
 |-------------------------------------------------------------------------
 |  Application
 |-------------------------------------------------------------------------
*/
$config['language'] = 'italiano';		// You can specify it in the controller with c::set


/* End of file config.development.php
  Location: ./system/config/config.development.php */