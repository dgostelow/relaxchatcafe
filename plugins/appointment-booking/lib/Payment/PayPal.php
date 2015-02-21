<?php

/**
 * Need to add this action to listen all requests from
 * the PayPal site (the Paypal redirects back for Express Checkout)
 */
add_action( 'wp_loaded', array( new PayPal(), 'responseListener' ) );

/**
 *
 */
class PayPal {

    /**
     * The array of products for checkout
     *
     * @var array
     */
    protected $products = array();

    public static function getCurrencyCodes() {
        return array( 'AUD', 'BRL', 'CAD', 'RMB', 'CZK', 'DKK', 'EUR', 'GTQ', 'HKD', 'HUF', 'IDR', 'INR', 'ILS', 'JPY', 'KRW', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RON', 'RUB', 'SGD', 'ZAR', 'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD' );
    }

    /**
     * Entry point for all requests from PayPal
     */
    public function responseListener() {
        if ( isset( $_GET[ 'action' ] ) ) {
            switch ( $_GET[ 'action' ] ) {
                // process the Express Checkout redirects from the PayPal
                case 'ab-paypal-returnurl':
                    $this->process_EC_ReturnUrl( $_GET[ 'ab_fid' ] );
                    break;
                case 'ab-paypal-cancelurl':
                    $this->process_EC_CancelUrl( $_GET[ 'ab_fid' ] );
                    break;
                case 'ab-paypal-errorurl':
                    $this->process_EC_ErrorUrl( $_GET[ 'ab_fid' ] );
                    break;
            }
        }
    }

    /**
     * Send the Express Checkout NVP request
     *
     * @param $form_id
     * @throws Exception
     */
    public function send_EC_Request( $form_id ) {
        if ( !session_id() ) {
            @session_start();
        }

        if ( ! count( $this->products ) ) {
            throw new Exception('Products not found!');
        }

        $total = 0;

        // create the data to send on PayPal
        $data =
            '&SOLUTIONTYPE='                   . 'Sole'.
            '&PAYMENTREQUEST_0_PAYMENTACTION=' . 'Sale'.
            '&PAYMENTREQUEST_0_CURRENCYCODE='  . urlencode( get_option( 'ab_paypal_currency' ) ) .
            '&RETURNURL='. urlencode( add_query_arg( array( 'action' => 'ab-paypal-returnurl', 'ab_fid' => $form_id), AB_CommonUtils::getCurrentPageURL() ) ) .
            '&CANCELURL='. urlencode( add_query_arg( array( 'action' => 'ab-paypal-cancelurl', 'ab_fid' => $form_id), AB_CommonUtils::getCurrentPageURL() ) );

        foreach ( $this->products as $k => $product ) {
            $data .=
                "&L_PAYMENTREQUEST_0_NAME{$k}=".urlencode($product->name).
                "&L_PAYMENTREQUEST_0_DESC{$k}=".urlencode($product->desc).
                "&L_PAYMENTREQUEST_0_AMT{$k}=".urlencode($product->price).
                "&L_PAYMENTREQUEST_0_QTY{$k}=".urlencode($product->qty);

            $total += ($product->qty * $product->price);
        }
        $data .=
            "&PAYMENTREQUEST_0_AMT=".urlencode($total).
            "&PAYMENTREQUEST_0_ITEMAMT=".urlencode($total);

        $_SESSION['ab_payment_total'] = $total;

        // send the request to PayPal
        $response = self::sendNvpRequest('SetExpressCheckout', $data);

        //Respond according to message we receive from Paypal
        if ( "SUCCESS" == strtoupper( $response["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $response["ACK"] ) ) {
            $_SESSION['appointment_booking'][$form_id]['pay_pal_response'] = array( $response, $form_id );

            $paypalurl ='https://www'.get_option( 'ab_paypal_ec_mode' ).'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token='.urldecode( $response["TOKEN"] );
            header('Location: '.$paypalurl);
            exit;
        } else {
            header('Location: ' . add_query_arg( array( 'action' => 'ab-paypal-errorurl', 'ab_fid' => $form_id, 'error_msg' => $response["L_LONGMESSAGE0"]), AB_CommonUtils::getCurrentPageURL() ) );
            exit;
        }
    }

    /**
     * Process the Express Checkout RETURNURL
     */
    public function process_EC_ReturnUrl( $form_id ) {
        if ( !session_id() ) {
            @session_start();
        }

        if ( isset( $_GET["token"] ) && isset( $_GET["PayerID"] ) ) {
            $token    = $_GET["token"];
            $payer_id = $_GET["PayerID"];

            // send the request to PayPal
            $response = self::sendNvpRequest( 'GetExpressCheckoutDetails', sprintf( '&TOKEN=%s', $token ) );

            if ( strtoupper( $response["ACK"] ) == "SUCCESS" ) {
                $data = sprintf( '&TOKEN=%s&PAYERID=%s&PAYMENTREQUEST_0_PAYMENTACTION=Sale', $token, $payer_id );

                // response keys containing useful data to send via DoExpressCheckoutPayment operation
                $response_data_keys_pattern = sprintf( '/^(%s)/', implode( '|', array(
                    'PAYMENTREQUEST_0_AMT',
                    'PAYMENTREQUEST_0_ITEMAMT',
                    'PAYMENTREQUEST_0_CURRENCYCODE',
                    'L_PAYMENTREQUEST_0',
                ) ) );

                foreach ( $response as $key => $value ) {
                    // collect product data from response using defined response keys
                    if ( preg_match( $response_data_keys_pattern, $key ) ) {
                        $data .= sprintf( '&%s=%s', $key, $value );
                    }
                }

                //We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
                $response = self::sendNvpRequest( 'DoExpressCheckoutPayment', $data );
                if ( "SUCCESS" == strtoupper( $response["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $response["ACK"] ) ) {
                    // get transaction info
                    $response = self::sendNvpRequest( 'GetTransactionDetails', "&TRANSACTIONID=" . urlencode( $response["PAYMENTINFO_0_TRANSACTIONID"] ) );
                    if ( "SUCCESS" == strtoupper( $response["ACK"] ) || "SUCCESSWITHWARNING" == strtoupper( $response["ACK"] ) ) {
                        do_action( 'ab_paypal_order_accepted', array( $response, $form_id ) );
                        return true;
                    } else {
                        header('Location: ' . add_query_arg( array( 'action' => 'ab-paypal-errorurl', 'ab_fid' => $form_id, 'error_msg' => $response["L_LONGMESSAGE0"]), AB_CommonUtils::getCurrentPageURL() ) );
                        exit;
                    }
                } else {
                    header('Location: ' . add_query_arg( array( 'action' => 'ab-paypal-errorurl', 'ab_fid' => $form_id, 'error_msg' => $response["L_LONGMESSAGE0"]), AB_CommonUtils::getCurrentPageURL() ) );
                    exit;
                }
            } else {
                header('Location: ' . add_query_arg( array( 'action' => 'ab-paypal-errorurl', 'ab_fid' => $form_id, 'error_msg' => 'Invalid token provided' ), AB_CommonUtils::getCurrentPageURL() ) );
                exit;
            }
        } else {
            throw new Exception('Token parameter not found!');
        }
    }

    /**
     * Process the Express Checkout CANCELURL
     */
    public function process_EC_CancelUrl() {
        if ( !session_id() ) {
            @session_start();
        }
        $form_id = null;
        if ( isset( $_GET[ 'token'] ) ) {
            unset( $_SESSION[ 'ab_payment_total' ] );

            $last_appointment = end( $_SESSION[ 'appointment_booking' ] );
            $form_id = $last_appointment[ 'form_id' ];

            do_action( 'ab_paypal_order_cancel' , @$_GET[ 'form_id' ] ? @urldecode( @$_GET[ 'form_id' ] ) : @$form_id );
        } else {
            throw new Exception('Token parameter not found!');
        }
    }

    /**
     * Process the Express Checkout ERROR
     */
    public function process_EC_ErrorUrl() {
        if ( !session_id() ) {
            @session_start();
        }
        $form_id = null;
        unset( $_SESSION[ 'ab_payment_total' ] );

        $last_appointment = end( $_SESSION[ 'appointment_booking' ] );
        $form_id = $last_appointment[ 'form_id' ];

        do_action( 'ab_paypal_order_error' , @$_GET[ 'form_id' ] ? @urldecode( @$_GET[ 'form_id' ] ) : @$form_id );
    }

    /**
     * Send the NVP Request to the PayPal
     *
     * @param $method
     * @param $nvpStr
     * @return array
     */
    private function sendNvpRequest($method, $nvpStr) {
        $username   = urlencode( get_option( 'ab_paypal_api_username' ) );
        $password   = urlencode( get_option( 'ab_paypal_api_password' ) );
        $signature  = urlencode( get_option( 'ab_paypal_api_signature' ) );

        $url = "https://api-3t".get_option( 'ab_paypal_ec_mode' ).".paypal.com/nvp";
        $version = urlencode('76.0');

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD={$method}&VERSION={$version}&PWD={$password}&USER={$username}&SIGNATURE={$signature}{$nvpStr}";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // Get response from the server.
        $httpResponse = curl_exec($ch);

        if(!$httpResponse) {
            exit("$method failed: ".curl_error($ch).'('.curl_errno($ch).')');
        }

        // Extract the response details.
        $httpResponseArray = explode("&", $httpResponse);

        $httpParsedResponseArray = array();
        foreach ($httpResponseArray as $i => $value) {
            $tmpAr = explode("=", $value);
            if(sizeof($tmpAr) > 1) {
                $httpParsedResponseArray[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if((0 == sizeof($httpParsedResponseArray)) || !array_key_exists('ACK', $httpParsedResponseArray)) {
            exit("Invalid HTTP Response for POST request($nvpreq) to $url.");
        }

        return $httpParsedResponseArray;
    }

    public function renderForm( $form_id ) {
        $output = '<form method="post" class="ab-paypal-form">';
        $output .= '<input type="hidden" name="action" value="ab_paypal_checkout"/>';
        $output .= "<input type='hidden' name='form_id' value='{$form_id}'/>";
        $output .= '<button class="ab-left ab-to-third-step ab-btn ladda-button orange zoom-in" style="margin-right: 10px;"><span class="ab_label">' . __( 'Back', 'ab' ) . '</span><span class="spinner"></span></button>';
        $output .= '<button class="ab-right ab-final-step ab-btn ladda-button orange zoom-in"><span class="ab_label">' . __( 'Next', 'ab' ) . '</span><span class="spinner"></span></button>';
        $output .= '</form>';

        echo $output;
    }

    /**
     * @return array
     */
    public function getProducts() {
        return $this->products;
    }

    /**
     * Add the Product for payment
     *
     * @param stdClass $product
     */
    public function addProduct( stdClass $product ) {
        $this->products[] = $product;
    }
}