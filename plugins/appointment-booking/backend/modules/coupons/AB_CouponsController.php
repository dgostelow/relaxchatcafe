<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'forms/AB_CouponForm.php';

/**
 * Class AB_CouponsController
 */
class AB_CouponsController extends AB_Controller {

    /**
     *
     */
    public function index() {
        $this->coupons_collection  = $this->getCouponsCollection();

        $this->render( 'index' );
    }

    /**
     *
     */
    public function executeAddCoupon() {
        $form = new AB_CouponForm();
        $form->bind( $this->getPostParameters() );

        $form->save();

        $this->coupons_collection  = $this->getCouponsCollection();
        $this->render( 'list' );
        exit;
    }

    /**
     *
     */
    public function executeUpdateCouponValue() {
        $form = new AB_CouponForm();
        $form->bind( $this->getPostParameters() );

        if ( $this->getParameter( 'discount' ) < 0 || $this->getParameter( 'discount' ) > 100 ) {
            exit(json_encode(array(
                'status' => 'error',
                'text'   => __( 'Discount should be between 0 and 100', 'ab' )
            )));
        }
        else {
            $form->save();
            $this->coupons_collection  = $this->getCouponsCollection();

            exit ( json_encode( array(
                'status' => 'success',
                'text'   => $this->render( 'list', array(), false )
            )));
        }
    }

    /**
     *
     */
    public function executeRemoveCoupon() {
        $coupon_ids = $this->getParameter( 'coupon_ids', array() );
        if ( is_array( $coupon_ids ) && ! empty( $coupon_ids ) ) {
            $this->getWpdb()->query('DELETE FROM `ab_coupons` WHERE `id` IN (' . implode(', ', $coupon_ids) . ')');
        }
    }

    /**
     * @return mixed
     */
    private function getCouponsCollection() {
        return $this->getWpdb()->get_results( "SELECT * FROM `ab_coupons`" );
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}
