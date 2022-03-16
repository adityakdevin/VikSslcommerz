<?php
/**
 * @package     Viksslcommerz
 * @subpackage  vikbooking
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2018 VikWP All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

JLoader::import( 'sslcommerz', VIKSSLCOMMERZ_DIR );
add_action( 'payment_after_begin_transaction_vikbooking', function ( &$payment, &$html ) {
	if ( ! $payment->isDriver( 'sslcommerz' ) ) {
		return;
	}
	if ( $payment->get( 'leave_deposit' ) ) {
		$html = '<p class="vbo-leave-deposit">
			<span>' . JText::_( 'VBLEAVEDEPOSIT' ) . '</span>' .
		        $payment->get( 'currency_symb' ) . ' ' . number_format( $payment->get( 'total_to_pay' ), 2 ) .
		        '</p><br/>' . $html;
	}
	$was_using_cache = wp_using_ext_object_cache( false );
	$transient = set_transient( 'viksslcommerz_vikbooking_' . $payment->get( 'oid' ) . '_' . $payment->get( 'sid' ), $payment->get( 'total_to_pay' ), 10 * MINUTE_IN_SECONDS );
	wp_using_ext_object_cache( $was_using_cache );
	if ( ! $transient ) {
		$txname = $payment->get( 'sid' ) . '-' . $payment->get( 'oid' ) . '.tx';
		$fp     = fopen( VIKSSLCOMMERZ_DIR . DIRECTORY_SEPARATOR . 'VikSslcommerz' . DIRECTORY_SEPARATOR . $txname, 'w+' );
		fwrite( $fp, $payment->get( 'total_to_pay' ) );
		fclose( $fp );
	}
}, 10, 2 );

add_action( 'payment_before_validate_transaction_vikbooking', function ( $payment ) {
	if ( ! $payment->isDriver( 'sslcommerz' ) ) {
		return;
	}
	$txname = $payment->get( 'sid' ) . '-' . $payment->get( 'oid' ) . '.tx';
	$txdata = '';
	$path = VIKSSLCOMMERZ_DIR . DIRECTORY_SEPARATOR . 'Sslcommerz' . DIRECTORY_SEPARATOR . $txname;
	$was_using_cache = wp_using_ext_object_cache( false );
	$transient = 'viksslcommerz_vikbooking_' . $payment->get( 'oid' ) . '_' . $payment->get( 'sid' );
	$data = get_transient( $transient );
	if ( $data ) {
		$payment->set( 'total_to_pay', $data );
		delete_transient( $transient );
		wp_using_ext_object_cache( $was_using_cache );
	} else if ( is_file( $path ) ) {
		$fp     = fopen( $path, 'rb' );
		$txdata = fread( $fp, filesize( $path ) );
		fclose( $fp );
		if ( ! empty( $txdata ) ) {
			$payment->set( 'total_to_pay', $txdata );
		} else {
			$payment->set( 'total_to_pay', $payment->get( 'total_to_pay', 0 ) );
		}
		unlink( $path );
	}
} );

add_action( 'payment_on_after_validation_vikbooking', function ( &$payment, $res ) {
	if ( ! $payment->isDriver( 'sslcommerz' ) ) {
		return;
	}
	$url = 'index.php?option=com_vikbooking&task=vieworder&sid=' . $payment->get( 'sid' ) . '&ts=' . $payment->get( 'ts' );
	$model  = JModel::getInstance( 'vikbooking', 'shortcodes', 'admin' );
	$item_id = $model->all( 'post_id' );
	if ( count( $item_id ) ) {
		$url = JRoute::_( $url . '&Itemid=' . $item_id[0]->post_id, false );
	}
	JFactory::getApplication()->redirect( $url );
	exit;
}, 10, 2 );

class VikBookingSslCommerzPayment extends AbstractSslCommerzPayment {
	public function __construct( $alias, $order, $params = array() ) {
		parent::__construct( $alias, $order, $params );
		$details = $this->get( 'details', array() );
		$this->set( 'oid', $this->get( 'id', null ) );
		if ( ! $this->get( 'oid' ) ) {
			$this->set( 'oid', $details['id'] ?? 0 );
		}

		if ( ! $this->get( 'sid' ) ) {
			$this->set( 'sid', $details['sid'] ?? 0 );
		}

		if ( ! $this->get( 'ts' ) ) {
			$this->set( 'ts', $details['ts'] ?? 0 );
		}

		if ( ! $this->get( 'custmail' ) ) {
			$this->set( 'custmail', $details['custmail'] ?? '' );
		}
	}
}
