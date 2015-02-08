<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH.'/lib/entities/AB_Payment.php';
include AB_PATH.'/lib/Payment/PayPal.php';

/**
 * Class AB_PayPalController
 */
class AB_PayPalController extends AB_Controller {

    protected function getPermissions() {
        return array(
          '_this' => 'anonymous',
        );
    }

    public function __construct() {
        parent::__construct();

        $this->paypal = new PayPal();

        // customer accepted the order on PayPal and return their info
        add_action( 'ab_paypal_order_accepted', array ( $this, 'paypalResponseSuccess' ) );
        // customer canceled the order on PayPal and redirected to it
        add_action( 'ab_paypal_order_cancel', array ( $this, 'paypalResponseCancel' ) );

        add_action( 'ab_paypal_order_error', array ( $this, 'paypalResponseError' ) );
    }

    public function paypalExpressCheckout() {
        $form_id = $this->getParameter( 'form_id' );
        if ( $form_id ) {
            // create a paypal object
            $paypal = new PayPal();
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() && $userData->getServiceId() ) {
                $employee = new AB_Staff();
                $employee->load( $userData->getStaffId() );

                $service = new AB_Service();
                $service->load( $userData->getServiceId() );

                $price = $this->getWpdb()->get_var( $this->getWpdb()->prepare(
                    'SELECT price FROM ab_staff_service WHERE staff_id = %d AND service_id = %d',
                        $employee->get( 'id' ), $service->get( 'id' )
                ) );

                if ($userData->getCoupon()) {
                    $price = AB_Coupon::applyCouponOnPrice($userData->getCoupon(), $price);
                }

                // get the products information from the $_POST and create the Product objects
                $product = new stdClass();
                $product->name  = $service->get( 'title' );
                $product->desc  = $service->getTitleWithDuration();
                $product->price = $price;
                $product->qty   = 1;
                $paypal->addProduct($product);

                // and send the payment request
                $paypal->send_EC_Request( $form_id );
            } elseif ( isset( $_SESSION[ 'appointment_booking' ][ $form_id ], $_SESSION[ 'tmp_booking_data'] ) &&
                    $_SESSION[ 'appointment_booking' ][ $form_id ][ 'cancelled' ] === true
            ) {
                $tmp_booking_data = AB_CommonUtils::getTemporaryBookingData();

                if ( !empty( $tmp_booking_data ) ) {
                    $employee = new AB_Staff();
                    $employee->load( $tmp_booking_data[ 'staff_id' ][ 0 ] );

                    $service = new AB_Service();
                    $service->load( $tmp_booking_data[ 'service_id' ] );

                    $price = $this->getWpdb()->get_var( $this->getWpdb()->prepare( '
                            SELECT price FROM ab_staff_service WHERE staff_id = %d AND service_id = %d',
                        $employee->get( 'id' ), $service->get( 'id' )
                    ) );

                    if (isset($tmp_booking_data['coupon']) && $tmp_booking_data['coupon']){
                        $price = AB_Coupon::applyCouponOnPrice($tmp_booking_data['coupon'], $price);
                    }

                    // get the products information from the $_POST and create the Product objects
                    $product = new stdClass();
                    $product->name  = $service->get( 'title' );
                    $product->desc  = $service->getTitleWithDuration();
                    $product->price = $price;
                    $product->qty   = 1;
                    $paypal->addProduct($product);

                    // and send the payment request
                    $paypal->send_EC_Request( $form_id );
                }
            }
        }
    }

    /**
     * Express Checkout 'RETURNURL' process
     *
     * @param $data
     */
    public function paypalResponseSuccess( $data ) {

        list( $response, $form_id ) = $data;

        // need session to get Total and Token

        $token = $_SESSION[ 'appointment_booking' ][ $form_id ][ 'pay_pal_response' ][ 0 ][ 'TOKEN' ];

        $userData = new AB_UserBookingData( $form_id );
        $userData->load();

        if ( $userData->hasData() && $userData->getServiceId() ) {
            $appointment = $userData->save();

            $customer_appointment = new AB_Customer_Appointment();
            $customer_appointment->loadBy( array(
                'appointment_id' => $appointment->get('id'),
                'customer_id'    => $userData->getCustomerId()
            ) );

            $payment = new AB_Payment();
            $payment->set( 'token', urldecode($token) );
            $payment->set( 'total', isset($_SESSION['ab_payment_total']) ? urlencode($_SESSION['ab_payment_total']) : '0.00' );
            $payment->set( 'customer_id', $customer_appointment->get( 'customer_id' ) );
            $payment->set( 'appointment_id', $appointment->get( 'id' ) );
            $payment->set( 'transaction', urlencode( $response["TRANSACTIONID"] ) );
            $payment->set( 'created', date('Y-m-d H:i:s') );

            if ($userData->getCoupon()){
                $payment->set( 'coupon', $userData->getCoupon());
                AB_Coupon::useCoupon($userData->getCoupon());
            }

            $payment->save();

            if ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
                unset( $_SESSION[ 'tmp_booking_data' ] );
            }
            $_SESSION[ 'tmp_booking_data' ] = serialize( $userData );

            $userData->clean();
            $userData->setPaymentId( $payment->get( 'id' ) );
            $userData->setBookingFinished( true );

        } elseif ( isset( $_SESSION[ 'appointment_booking' ][ $form_id ], $_SESSION[ 'tmp_booking_data'] ) &&
            @$_SESSION[ 'appointment_booking' ][ $form_id ][ 'cancelled' ] === true
        ) {
            $tmp_booking_data = AB_CommonUtils::getTemporaryBookingData();

            if ( !empty( $tmp_booking_data ) ) {
                $userData = new AB_UserBookingData( $form_id );
                $userData->loadTemporaryForExpressCheckout();

                if ( $userData->hasData() && $userData->getServiceId() ) {
                    $appointment = $userData->save();

                    $customer_appointment = new AB_Customer_Appointment();
                    $customer_appointment->loadBy( array(
                        'appointment_id' => $appointment->get('id'),
                        'customer_id'    => $userData->getCustomerId()
                    ) );

                    $payment = new AB_Payment();
                    $payment->set( 'token', urldecode($response['TOKEN']) );
                    $payment->set( 'total', isset($_SESSION['ab_payment_total']) ? urlencode($_SESSION['ab_payment_total']) : '0.00' );
                    $payment->set( 'customer_id', $customer_appointment->get( 'customer_id' ) );
                    $payment->set( 'appointment_id', $appointment->get( 'id' ) );
                    $payment->set( 'created', date('Y-m-d H:i:s') );

                    if ($userData->getCoupon()){
                        $payment->set('coupon', $userData->getCoupon());
                        AB_Coupon::useCoupon($userData->getCoupon());
                    }

                    $payment->save();

                    if ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
                        unset( $_SESSION[ 'tmp_booking_data' ] );
                    }
                    $_SESSION[ 'tmp_booking_data' ] = serialize( $userData );

                    $userData->clean();
                    $userData->setPaymentId( $payment->get( 'id' ) );
                }
            }
        }

        $userData->setBookingFinished( true );
        @wp_safe_redirect( site_url( remove_query_arg( array( 'action', 'token', 'PayerID', 'form_id' ) ) ) );
        exit;
    }

    /**
     * Express Checkout 'CANCELURL' process
     *
     * @param $form_id
     */
    public function paypalResponseCancel( $form_id ) {
        $userData = new AB_UserBookingData( $form_id );
        $userData->load();
        $userData->setBookingCancelled( true );
        @wp_safe_redirect( site_url( remove_query_arg( array( 'action', 'token', 'PayerID', 'form_id' ) ) ) );
        exit;
    }

    /**
     * Express Checkout 'ERRORURL' process
     *
     * @param $form_id
     */
    public function paypalResponseError( $form_id ) {
        $userData = new AB_UserBookingData( $form_id );
        $userData->load();
        $userData->setBookingPayPalError( $this->getParameter( 'error_msg' ) );
        $userData->setBookingCancelled( true );
        @wp_safe_redirect( site_url( remove_query_arg( array( 'action', 'token', 'PayerID', 'form_id', 'error_msg' ) ) ) );
        exit;
    }
}