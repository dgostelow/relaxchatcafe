<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'AuthorizeNet' ) ) include AB_PATH.'/lib/Payment/authorize.net/AuthorizeNet.php';

/**
 * Class AB_AuthorizeNetController
 */
class AB_AuthorizeNetController extends AB_Controller {

    protected function getPermissions() {
        return array(
            '_this' => 'anonymous',
        );
    }

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        // Init Authorize.net class autoload.
        new AuthorizeNet();
    }

    /**
     * Do AIM payment.
     */
    public function executeAuthorizeNetAIM() {
        $form_id = $this->getParameter( 'form_id' );
        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() && $userData->getServiceId() ) {
                define( "AUTHORIZENET_API_LOGIN_ID", get_option( 'ab_authorizenet_api_login_id' ) );
                define( "AUTHORIZENET_TRANSACTION_KEY", get_option( 'ab_authorizenet_transaction_key' ) );
                define( "AUTHORIZENET_SANDBOX", (bool)get_option( 'ab_authorizenet_sandbox' ) );

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

                $sale               = new AuthorizeNetAIM();
                $sale->amount       = $price;
                $sale->card_num     = $this->getParameter( 'ab_card_number' );
                $sale->card_code    = $this->getParameter( 'ab_card_code' );
                $sale->exp_date     = $this->getParameter( 'ab_card_month' ) . '/' . $this->getParameter( 'ab_card_year' );
                $sale->first_name   = $userData->getName();
                $sale->email        = $userData->getEmail();
                $sale->phone        = $userData->getPhone();

                $response = $sale->authorizeAndCapture();
                if ($response->approved) {
                    /** @var AB_Appointment $appointment */
                    $appointment = $userData->save();

                    $customer_appointment = new AB_Customer_Appointment();
                    $customer_appointment->loadBy( array(
                        'appointment_id' => $appointment->get('id'),
                        'customer_id'    => $userData->getCustomerId()
                    ) );

                    $payment = new AB_Payment();
                    $payment->set( 'total', $price);
                    $payment->set( 'type', 'authorizeNet' );
                    $payment->set( 'customer_id', $customer_appointment->get( 'customer_id' ) );
                    $payment->set( 'appointment_id', $appointment->get( 'id' ) );
                    $payment->set( 'created', date('Y-m-d H:i:s') );

                    if ($userData->getCoupon()) {
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
                    $userData->setBookingFinished( true );
                    echo json_encode ( array ( 'state' => 'true' ) );
                } else {
                    echo json_encode ( array ( 'error' => $response->response_reason_text ) );
                }
            }
        }

        exit();
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
        parent::registerWpActions( 'wp_ajax_nopriv_ab_' );
    }
}
