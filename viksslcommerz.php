<?php
/*
Plugin Name:  VikSslCommerz
Description:  SslCommerz integration to collect payments through the Vik plugins
Version:      1.0.0
Author:       SslCommerz Integration Department.
Author URI:   https://sslcommerz.io
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  viksslcommerz
Domain Path:  /languages
*/


// No direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// require utils functions
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'utils.php';
define( 'VIKSSLCOMMERZ_LANG', basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'languages' );
define( 'VIKSSLCOMMERZ_LIB', basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lib' );

define( 'VIKSSLCOMMERZVERSION', '1.0.0' );

add_action( 'init', function () {
	JFactory::getLanguage()->load( 'viksslcommerz', VIKSSLCOMMERZ_LANG );
} );

/**
 * Returns the current plugin version for being used by
 * VikUpdater, in order to check for new updates.
 *
 * @return    string    The plugin version.
 */
add_filter( 'vikwp_vikupdater_sslcommerz_version', function () {
	return VIKSSLCOMMERZVERSION;
} );

/**
 * Returns the base path of this plugin for being used by
 * VikUpdater, in order to move the files of a newer version.
 *
 * @return    string    The plugin base path.
 */
add_filter( 'vikwp_vikupdater_sslcommerz_path', function () {
	return plugin_dir_path( __FILE__ ) . "..";
} );

/**
 * VIKRESTAURANTS HOOKS
 */

/**
 * Pushes the sslcommerz gateway within the supported payments of VikRentCar plugin.
 *
 * @param array $drivers The current list of supported drivers.
 *
 * @return    array    The updated drivers list.
 */
add_filter( 'get_supported_payments_vikrestaurants', function ( $drivers ) {
	$driver = viksslcommerz_get_payment_path( 'vikrestaurants' );

	// make sure the driver exists
	if ( $driver ) {
		$drivers[] = $driver;
	}

	return $drivers;
} );

/**
 * Loads sslcommerz payment handler when dispatched by Vikrestaurants.
 *
 * @param array    &$drivers A list of payment classnames.
 * @param string $payment The name of the invoked payment.
 *
 * @return    void
 *
 * @see    JPayment
 */
add_action( 'load_payment_gateway_vikrestaurants', function ( &$drivers, $payment ) {
	// make sure the classname hasn't been generated yet by a different hook
	// and the request payment matches 'kashier' string
	if ( $payment == 'sslcommerz' ) {
		$classname = viksslcommerz_load_payment( 'vikrestaurants' );

		if ( $classname ) {
			$drivers[] = $classname;
		}
	}
}, 10, 2 );

/**
 * VIKRENTITEMS HOOKS
 */

/**
 * Pushes the sslcommerz gateway within the supported payments of VikRentCar plugin.
 *
 * @param array $drivers The current list of supported drivers.
 *
 * @return    array    The updated drivers list.
 */
add_filter( 'get_supported_payments_vikrentitems', function ( $drivers ) {
	$driver = viksslcommerz_get_payment_path( 'vikrentitems' );

	// make sure the driver exists
	if ( $driver ) {
		$drivers[] = $driver;
	}

	return $drivers;
} );

/**
 * Loads sslcommerz payment handler when dispatched by vikrentitems.
 *
 * @param array    &$drivers A list of payment classnames.
 * @param string $payment The name of the invoked payment.
 *
 * @return    void
 *
 * @see    JPayment
 */
add_action( 'load_payment_gateway_vikrentitems', function ( &$drivers, $payment ) {
	// make sure the classname hasn't been generated yet by a different hook
	// and the request payment matches 'sslcommerz' string
	if ( $payment == 'sslcommerz' ) {
		$classname = viksslcommerz_load_payment( 'vikrentitems' );

		if ( $classname ) {
			$drivers[] = $classname;
		}
	}
}, 10, 2 );

/**
 * VIKRENTCAR HOOKS
 */

/**
 * Pushes the sslcommerz gateway within the supported payments of VikRentCar plugin.
 *
 * @param array $drivers The current list of supported drivers.
 *
 * @return    array    The updated drivers list.
 */
add_filter( 'get_supported_payments_vikrentcar', function ( $drivers ) {
	$driver = viksslcommerz_get_payment_path( 'vikrentcar' );

	// make sure the driver exists
	if ( $driver ) {
		$drivers[] = $driver;
	}

	return $drivers;
} );

/**
 * Loads sslcommerz payment handler when dispatched by Vikrentcar.
 *
 * @param array    &$drivers A list of payment classnames.
 * @param string $payment The name of the invoked payment.
 *
 * @return    void
 *
 * @see    JPayment
 */
add_action( 'load_payment_gateway_vikrentcar', function ( &$drivers, $payment ) {
	// make sure the classname hasn't been generated yet by a different hook
	// and the request payment matches 'sslcommerz' string
	if ( $payment == 'sslcommerz' ) {
		$classname = viksslcommerz_load_payment( 'vikrentcar' );

		if ( $classname ) {
			$drivers[] = $classname;
		}
	}
}, 10, 2 );

/**
 * VIKAPPOINTMENTS HOOKS
 */

/**
 * Pushes the sslcommerz gateway within the supported payments of VikAppointments plugin.
 *
 * @param array $drivers The current list of supported drivers.
 *
 * @return    array    The updated drivers list.
 */
add_filter( 'get_supported_payments_vikappointments', function ( $drivers ) {
	$driver = viksslcommerz_get_payment_path( 'vikappointments' );

	// make sure the driver exists
	if ( $driver ) {
		$drivers[] = $driver;
	}

	return $drivers;
} );

/**
 * Loads sslcommerz payment handler when dispatched by VikAppointments.
 *
 * @param array    &$drivers A list of payment classnames.
 * @param string $payment The name of the invoked payment.
 *
 * @return    void
 *
 * @see    JPayment
 */
add_action( 'load_payment_gateway_vikappointments', function ( &$drivers, $payment ) {
	// make sure the classname hasn't been generated yet by a different hook
	// and the request payment matches 'sslcommerz' string
	if ( $payment == 'sslcommerz' ) {
		$classname = viksslcommerz_load_payment( 'vikappointments' );

		if ( $classname ) {
			$drivers[] = $classname;
		}
	}
}, 10, 2 );

/**
 * VIKBOOKING HOOKS
 */

/**
 * Pushes the sslcommerz gateway within the supported payments of VikBooking plugin.
 *
 * @param array $drivers The current list of supported drivers.
 *
 * @return    array    The updated drivers list.
 */
add_filter( 'get_supported_payments_vikbooking', function ( $drivers ) {
	$driver = viksslcommerz_get_payment_path( 'vikbooking' );

	// make sure the driver exists
	if ( $driver ) {
		$drivers[] = $driver;
	}

	return $drivers;
} );

/**
 * Loads sslcommerz payment handler when dispatched by VikBooking.
 *
 * @param array    &$drivers A list of payment instances.
 * @param string $payment The name of the invoked payment.
 *
 * @return    void
 *
 * @see    JPayment
 */
add_action( 'load_payment_gateway_vikbooking', function ( &$drivers, $payment ) {
	// make sure the classname hasn't been generated yet by a different hook
	// and the request payment matches 'sslcommerz' string
	if ( $payment == 'sslcommerz' ) {
		$classname = viksslcommerz_load_payment( 'vikbooking' );

		if ( $classname ) {
			$drivers[] = $classname;
		}
	}
}, 10, 2 );

/**
 * Filters the array containing the logo details to let VikBooking
 * retrieves the correct image.
 *
 * In order to change the image logo, it is needed to inject the
 * image path and URI within the $logo argument.
 *
 * @param array $logo An array containing the following information:
 *                            - name  The payment name;
 *                            - path  The payment logo base path;
 *                            - uri    The payment logo base URI.
 *
 * @return    array    The updated logo information.
 */
add_filter( 'vikbooking_oconfirm_payment_logo', function ( $logo ) {
	if ( $logo['name'] == 'sslcommerz' ) {
		$logo['path'] = VIKKASHIER_DIR . DIRECTORY_SEPARATOR . 'vikbooking' . DIRECTORY_SEPARATOR . 'sslcommerz.png';
		$logo['uri']  = VIKKASHIER_URI . 'vikbooking/sslcommerz.png';
	}

	return $logo;
} );
