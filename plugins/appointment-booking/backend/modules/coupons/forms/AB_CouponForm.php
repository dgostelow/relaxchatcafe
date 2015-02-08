<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH . '/lib/entities/AB_Coupon.php';

/**
 * Class AB_CouponForm
 */
class AB_CouponForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_Coupon';
        parent::__construct();
    }

    public function configure() {
        $this->setFields(array('id', 'code', 'discount'));
    }
}