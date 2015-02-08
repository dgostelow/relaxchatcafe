<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Stripe' ) ) include AB_PATH.'/lib/Payment/stripe/Stripe.php';

/**
 * Class AB_StripeController
 */
class AB_StripeController extends AB_Controller {

    protected function getPermissions() {
        return array(
          '_this' => 'anonymous',
        );
    }

    public function executeStripe() {
        $form_id = $this->getParameter( 'form_id' );
        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() && $userData->getServiceId() ) {
                Stripe::setApiKey(get_option( 'ab_stripe_secret_key' ));
                Stripe::setApiVersion("2014-10-07");

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

                $stripe_data = array(
                    'number'    => $this->getParameter( 'ab_card_number' ),
                    'exp_month' => $this->getParameter( 'ab_card_month' ),
                    'exp_year'  => $this->getParameter( 'ab_card_year' ),
                    'cvc'       => $this->getParameter( 'ab_card_code' ),
                );

                try{
                    $charge = Stripe_Charge::create(array(
                        'card' => $stripe_data,
                        'amount' => intval($price * 100), // amount incents
                        'currency' => get_option( 'ab_paypal_currency' ),
                        'description' => "Charge for " . $userData->getEmail(),
                    ));
                }catch(Exception $e){
                    echo json_encode(array('error' => $e->getMessage()));
                    exit();
                }

                if ($charge->paid){
                    $appointment = $userData->save();

                    $payment = new AB_Payment();
                    $payment->set( 'total', $price);
                    $payment->set( 'type', 'stripe' );
                    $payment->set( 'customer_id', $userData->getCustomerId() );
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
                }else{
                    echo json_encode ( array ( 'error' => 'unknown error' ) );
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
