<?php

/**
 * Class AB_Coupon
 */
class AB_Coupon extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_coupons';
        $this->schema = array(
            'id'          => array( 'format' => '%d' ),
            'code'        => array( 'format' => '%s', 'default' => '' ),
            'discount'    => array( 'format' => '%d', 'default' => 0 ),
            'used'        => array( 'format' => '%d', 'default' => 0 ),
        );
        parent::__construct();
    }

    /**
     * @param $coupon
     * @param $price
     * @return float
     */
    public static function applyCouponOnPrice($coupon, $price){
        /** @var WPDB $wpdb */
        global $wpdb;

        $discount = $wpdb->get_var( $wpdb->prepare(
            'SELECT `discount` FROM `ab_coupons` WHERE UPPER(`code`) = %s AND `used` = 0',
            strtoupper($coupon)
        ));

        if ($discount) {
            $price -= $price * $discount / 100;
        }

        return $price;
    }

    /**
     * @param $coupon
     */
    public static function useCoupon($coupon){
        /** @var WPDB $wpdb */
        global $wpdb;

        $wpdb->query($wpdb->prepare('UPDATE `ab_coupons` SET `used` = 1 WHERE UPPER(`code`) = %s', strtoupper($coupon)));
    }
}