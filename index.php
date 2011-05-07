<?php
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

// Some basic configuration
//----------------------------------------------------------------------------------------------

error_reporting(E_ALL|E_STRICT); // eg. E_ALL^E_NOTICE^E_USER_NOTICE in production

// Where are we (with a trailing slash)
define('SK_PATH', rtrim(realpath(dirname(__FILE__)), '/').'/');

// Environment (development|production|other...)
define('ENVIRONMENT', 'development');

// Skir system folder location (with a trailing slash)
define('SYSTEM', 'system/');

// Application location (with a trailing slash)
define('APPLICATION', 'application/');


// And away we go...
//----------------------------------------------------------------------------------------------
require SK_PATH.SYSTEM.'core/bootstrap.php';

/* End of file index.php
  Location: ./index.php */