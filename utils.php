<?php
/**
 * @package     VikSslCommercePay
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2018 SslCommercePay All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://sslcommercepay.io
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

// Define plugin base path
define('VIKSSLCOMMERZ_DIR', dirname(__FILE__));
// Define plugin base URI
define('VIKSSLCOMMERZ_URI', plugin_dir_url(__FILE__));

/**
 * Imports the file of the gateway and returns the classname
 * of the file that will be instantiated by the caller.
 *
 * @param 	string 	$plugin  The name of the caller.
 *
 * @return 	mixed 	The classname of the payment if exists, otherwise false.
 */
function viksslcommerz_load_payment($plugin)
{
	if (!JLoader::import("{$plugin}.sslcommerz", VIKSSLCOMMERZ_DIR))
	{
		return false;
	}

	return ucwords($plugin) . 'SslcommerzPayment';
}

/**
 * Returns the path in which the payment is located.
 *
 * @param 	string 	$plugin  The name of the caller.
 *
 * @return 	mixed 	The path if exists, otherwise false.
 */
function viksslcommerz_get_payment_path($plugin)
{
	$path = VIKSSLCOMMERZ_DIR . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'sslcommerz.php';

	if (!is_file($path))
	{
		return false;
	}

	return $path;
}
