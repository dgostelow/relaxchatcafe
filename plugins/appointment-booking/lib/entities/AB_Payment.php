<?php

/**
 * Class AB_Payment
 */
class AB_Payment extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_payment';
        $this->schema = array(
            'id'              => array( 'format' => '%d' ),
            'created'         => array( 'format' => '%s' ),
            'type'            => array( 'format' => '%s', 'default' => 'paypal' ),
            'customer_id'     => array( 'format' => '%d' ),
            'appointment_id'  => array( 'format' => '%d' ),
            'token'           => array( 'format' => '%s', 'default' => '' ),
            'transaction'     => array( 'format' => '%s', 'default' => '' ),
            'total'           => array( 'format' => '%.2f' ),
            'coupon'          => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

}