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
 |  Helpers
 |-------------------------------------------------------------------------
*/

// Dumps variables in a preformatted box. Can optionally kill the script.
function dump($item, $die = false)
{
    $print_string = '<pre>' . print_r($item, true) . '</pre>';
    if($die)
        die($print_string);
    else
        echo $print_string;
}


/*
 |-------------------------------------------------------------------------
 |  Classes
 |-------------------------------------------------------------------------
*/


/*
 |-------------------------------------------------------------------------
 |  Class aliases
 |-------------------------------------------------------------------------
*/


/* End of file toolkit.php
  Location: ./system/core/toolkit.php */