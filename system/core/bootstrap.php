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
 * System Front Controller
 * =======================
 *
 * Loads the configuration settings, initializes the environment
 * and executes the request.
 * ------------------------------------------------------------------------
 */

/*
 |-------------------------------------------------------------------------
 |  Load core classes
 |-------------------------------------------------------------------------
*/
	require SK_PATH.SYSTEM.'core/core.php';
	require SK_PATH.SYSTEM.'core/toolkit.php';

/*
 |-------------------------------------------------------------------------
 |  Load the configuration file
 |-------------------------------------------------------------------------
*/
	c::load('config.'.ENVIRONMENT.'.php');

/*
 |-------------------------------------------------------------------------
 |  Serve the requested resource
 |-------------------------------------------------------------------------
*/
	// Format the server request
	$request = isset($_GET['request']) ? trim($_GET['request'], '/').'/' : '';

	// Allow for controllers in folders
	is_dir(SK_PATH.APPLICATION.'controllers/'.substr($request, 0, strpos($request, '/')))
		? @list($folder, $controller, $method, $params) = explode('/', $request, 4)
		: @list($controller, $method, $params) = explode('/', $request, 3);

	// Find the real controller path
	$folder = !empty($folder) ? $folder.'/' : '';
	$controller = $controller ? $controller : c::get('default_controller');
	$controller_file = SK_PATH.APPLICATION.'controllers/'.$folder.$controller.'.php';

	if (!file_exists($controller_file))
	{
		v::error('404');
	}

	// Load and instantiate the controller
	require $controller_file;
	$SK = new $controller;

	if(!is_callable(array($SK, $method ? $method : 'index')))
	{
		v::error('404');
	}

	// Autoload libraries, models and helpers as per configuration
	foreach(array('library', 'model', 'helper') as $type)
	{
		$property = (array) c::get("autoload_$type");
		if (!empty($property[0]))
		{
			foreach($property as $class)
			{
				l::$type($class);
			}
		}
	}

	// Autoload the helper corresponding to the controller
	if (file_exists(SK_PATH.APPLICATION.'helpers/'.$controller.'_helper.php'))
	{
		l::helper($controller.'_helper');
	}

	// Execute!
	call_user_func_array(array($SK, $method ? $method : 'index'), $params ? explode('/', $params) : array());


/* End of file bootstrap.php
  Location: ./system/core/bootstrap.php */