<?php

require_once( VIKSSLCOMMERZ_DIR . "/lib/AbstractSslCommerz.php" );


class SslCommerzNotification extends AbstractSslCommerz {
	protected array $data = [];
	protected array $config = [];

	private string $successUrl = '';
	private string $cancelUrl = '';
	private string $failedUrl = '';
	private string $ipnUrl = '';

	private string $error = '';

	public function __construct( $store_id, $store_password, $api_domain ) {
		$this->setStoreId( $store_id );
		$this->setStorePassword( $store_password );
		$this->setApiDomain( $api_domain );
	}

	public function orderValidate( $trx_id = '', $amount = 0, $currency = "BDT", $post_data ): bool|string {
		if ( $post_data == '' && $trx_id == '' && ! is_array( $post_data ) ) {
			$this->error = "Please provide valid transaction ID and post request data";

			return $this->error;
		}
		$validation = $this->validate( $trx_id, $amount, $currency, $post_data );
		if ( $validation ) {
			return true;
		}

		return false;
	}


	# VALIDATE SSLCOMMERZ TRANSACTION
	protected function validate( $merchant_trans_id, $merchant_trans_amount, $merchant_trans_currency, $post_data ): bool {
		if ( $merchant_trans_id != "" && $merchant_trans_amount != 0 ) {
			$post_data['store_id']   = $this->getStoreId();
			$post_data['store_pass'] = $this->getStorePassword();
			if ( $this->SSLCOMMERZ_hash_varify( $this->getStorePassword(), $post_data ) ) {
				$val_id        = urlencode( $post_data['val_id'] );
				$store_id      = urlencode( $this->getStoreId() );
				$store_passwd  = urlencode( $this->getStorePassword() );
				$requested_url = ( $this->getApiDomain() . '/validator/api/validationserverAPI.php' . "?val_id=" . $val_id . "&store_id=" . $store_id . "&store_passwd=" . $store_passwd . "&v=1&format=json" );
				$handle        = curl_init();
				curl_setopt( $handle, CURLOPT_URL, $requested_url );
				curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
				if ( $post_data['connect_from_localhost'] ) {
					curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, false );
					curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, false );
				} else {
					curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, true );
					curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, true );
				}

				$result = curl_exec( $handle );
				$code   = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
				if ( $code == 200 && ! ( curl_errno( $handle ) ) ) {
					$result          = json_decode( $result );
					$this->sslc_data = $result;
					# TRANSACTION INFO
					$status          = $result->status;
					$tran_date       = $result->tran_date;
					$tran_id         = $result->tran_id;
					$val_id          = $result->val_id;
					$amount          = $result->amount;
					$store_amount    = $result->store_amount;
					$bank_tran_id    = $result->bank_tran_id;
					$card_type       = $result->card_type;
					$currency_type   = $result->currency_type;
					$currency_amount = $result->currency_amount;

					# ISSUER INFO
					$card_no                  = $result->card_no;
					$card_issuer              = $result->card_issuer;
					$card_brand               = $result->card_brand;
					$card_issuer_country      = $result->card_issuer_country;
					$card_issuer_country_code = $result->card_issuer_country_code;

					# API AUTHENTICATION
					$APIConnect   = $result->APIConnect;
					$validated_on = $result->validated_on;
					$gw_version   = $result->gw_version;

					# GIVE SERVICE
					if ( $status == "VALID" || $status == "VALIDATED" ) {
						if ( $merchant_trans_currency == "BDT" ) {
							if ( trim( $merchant_trans_id ) == trim( $tran_id ) && ( abs( $merchant_trans_amount - $amount ) < 1 ) && trim( $merchant_trans_currency ) == trim( 'BDT' ) ) {
								return true;
							}

							$this->error = "Data has been tempered";

							return false;
						}

						if ( trim( $merchant_trans_id ) == trim( $tran_id ) && ( abs( $merchant_trans_amount - $currency_amount ) < 1 ) && trim( $merchant_trans_currency ) == trim( $currency_type ) ) {
							return true;
						}
						$this->error = "Data has been tempered";

						return false;
					}

					$this->error = "Failed Transaction";

					return false;
				}

				$this->error = "Failed to connect with SSLCOMMERZ";

				return false;
			}

			$this->error = "Hash validation failed";

			return false;
		}

		$this->error = "Invalid data";

		return false;
	}

	protected function SSLCOMMERZ_hash_varify( $store_passwd = "", $post_data ) {

		if ( isset( $post_data, $post_data['verify_sign'], $post_data['verify_key'] ) ) {
			$pre_define_key = explode( ',', $post_data['verify_key'] );
			$new_data       = array();
			if ( ! empty( $pre_define_key ) ) {
				foreach ( $pre_define_key as $value ) {
					if ( isset( $post_data[ $value ] ) ) {
						$new_data[ $value ] = ( $post_data[ $value ] );
					}
				}
			}
			$new_data['store_passwd'] = md5( $store_passwd );
			ksort( $new_data );
			$hash_string = "";
			foreach ( $new_data as $key => $value ) {
				$hash_string .= $key . '=' . ( $value ) . '&';
			}
			$hash_string = rtrim( $hash_string, '&' );
			if ( md5( $hash_string ) == $post_data['verify_sign'] ) {
				return true;
			}
			$this->error = "Verification signature not matched";

			return false;
		}
		$this->error = 'Required data mission. ex: verify_key, verify_sign';

		return false;
	}

	public function makePayment( array $data, $type = 'checkout', $pattern = 'json' ) {
		if ( empty( $data ) ) {
			return "Please provide a valid information list about transaction with transaction id, amount, success url, fail url, cancel url, store id and pass at least";
		}
		$header = [];
		$this->setApiUrl( $this->getApiDomain() . '/gwprocess/v4/api.php' );
		$this->setParams( $data );
		$this->setAuthenticationInfo();
		$response          = $this->callToApi( $this->data, $header, true );
		$formattedResponse = $this->formatResponse( $response, $type, $pattern );
		if ( $type === 'hosted' ) {
			if ( isset( $formattedResponse['GatewayPageURL'] ) && $formattedResponse['GatewayPageURL'] != '' ) {
				$this->redirect( $formattedResponse['GatewayPageURL'] );
			} else {
				return $formattedResponse['failedreason'];
			}
		} else {
			return $formattedResponse;
		}
	}

	public function setParams( $data ) {
		$this->setRequiredInfo( $data );
		$this->setCustomerInfo( $data );
		$this->setShipmentInfo( $data );
		$this->setProductInfo( $data );
		$this->setAdditionalInfo( $data );
	}

	public function setRequiredInfo( array $data ): array {
		$this->data['total_amount']     = $data['total_amount'];
		$this->data['currency']         = $data['currency'];
		$this->data['tran_id']          = $data['tran_id'];
		$this->data['product_category'] = $data['product_category'];
		$this->setSuccessUrl( $data['success_url'] );
		$this->setFailedUrl( $data['fail_url'] );
		$this->setCancelUrl( $data['fail_url'] );
		if ( isset( $data['ipn_url'] ) ) {
			$this->setIpnUrl( $data['cancel_url'] );
		}
		$this->data['success_url']         = $this->getSuccessUrl();
		$this->data['fail_url']            = $this->getFailedUrl();
		$this->data['cancel_url']          = $this->getCancelUrl();
		$this->data['ipn_url']             = $this->getIpnUrl();
		$this->data['multi_card_name']     = $data['multi_card_name'] ?? null;
		$this->data['allowed_bin']         = $data['allowed_bin'] ?? null;
		$this->data['emi_option']          = $data['emi_option'] ?? null;
		$this->data['emi_max_inst_option'] = $data['emi_max_inst_option'] ?? null;
		$this->data['emi_selected_inst']   = $data['emi_selected_inst'] ?? null;

		return $this->data;
	}

	protected function getSuccessUrl() {
		return $this->successUrl;
	}

	protected function setSuccessUrl( $url ) {
		$this->successUrl = $url;
	}

	protected function getFailedUrl() {
		return $this->failedUrl;
	}

	protected function setFailedUrl( $url ) {
		$this->failedUrl = $url;
	}

	protected function getCancelUrl() {
		return $this->cancelUrl;
	}

	protected function setCancelUrl( $url ) {
		$this->cancelUrl = $url;
	}

	protected function getIpnUrl() {
		return $this->ipnUrl;
	}

	protected function setIpnUrl( $url ) {
		$this->ipnUrl = $url;
	}

	public function setCustomerInfo( array $data ): array {
		$this->data['cus_name']     = $data['cus_name'];
		$this->data['cus_email']    = $data['cus_email'];
		$this->data['cus_add1']     = $data['cus_add1'];
		$this->data['cus_add2']     = $data['cus_add2'] ?? null;
		$this->data['cus_city']     = $data['cus_city'] ?? null;
		$this->data['cus_state']    = $data['cus_state'] ?? null;
		$this->data['cus_postcode'] = $data['cus_postcode'] ?? '';
		$this->data['cus_country']  = $data['cus_country'];
		$this->data['cus_phone']    = $data['cus_phone'] ?? '';
		$this->data['cus_fax']      = $data['cus_fax'] ?? null;

		return $this->data;
	}

	public function setShipmentInfo( array $data ): array {
		$this->data['shipping_method'] = $data['shipping_method'];
		$this->data['num_of_item']     = $data['num_of_item'];
		$this->data['ship_name']       = $data['ship_name'] ?? null;
		$this->data['ship_add1']       = $data['ship_add1'] ?? null;
		$this->data['ship_add2']       = $data['ship_add2'] ?? null;
		$this->data['ship_city']       = $data['ship_city'] ?? null;
		$this->data['ship_state']      = $data['ship_state'] ?? null;
		$this->data['ship_postcode']   = $data['ship_postcode'] ?? null;
		$this->data['ship_country']    = $data['ship_country'] ?? null;

		return $this->data;
	}

	public function setProductInfo( array $data ): array {
		$this->data['product_name']         = $data['product_name'] ?? '';
		$this->data['product_category']     = $data['product_category'] ?? '';
		$this->data['product_profile']      = $data['product_profile'] ?? '';
		$this->data['hours_till_departure'] = $data['hours_till_departure'] ?? null;
		$this->data['flight_type']          = $data['flight_type'] ?? null;
		$this->data['pnr']                  = $data['pnr'] ?? null;
		$this->data['journey_from_to']      = $data['journey_from_to'] ?? null;
		$this->data['third_party_booking']  = $data['third_party_booking'] ?? null;
		$this->data['hotel_name']           = $data['hotel_name'] ?? null;
		$this->data['length_of_stay']       = $data['length_of_stay'] ?? null;
		$this->data['check_in_time']        = $data['check_in_time'] ?? null;
		$this->data['hotel_city']           = $data['hotel_city'] ?? null;
		$this->data['product_type']         = $data['product_type'] ?? null;
		$this->data['topup_number']         = $data['topup_number'] ?? null;
		$this->data['country_topup']        = $data['country_topup'] ?? null;
		$this->data['cart']                 = $data['cart'] ?? null;
		$this->data['product_amount']       = $data['product_amount'] ?? null;
		$this->data['vat']                  = $data['vat'] ?? null;
		$this->data['discount_amount']      = $data['discount_amount'] ?? null;
		$this->data['convenience_fee']      = $data['convenience_fee'] ?? null;

		return $this->data;
	}

	public function setAdditionalInfo( array $data ): array {
		$this->data['value_a'] = $data['value_a'] ?? null;
		$this->data['value_b'] = $data['value_b'] ?? null;
		$this->data['value_c'] = $data['value_c'] ?? null;
		$this->data['value_d'] = $data['value_d'] ?? null;

		return $this->data;
	}

	public function setAuthenticationInfo(): array {
		$this->data['store_id']     = $this->getStoreId();
		$this->data['store_passwd'] = $this->getStorePassword();

		return $this->data;
	}
}
