<?php

/**
 * Class AB_Customer_Appointment
 */
class AB_Customer_Appointment extends AB_Entity {

    /** @var AB_Customer */
    public $customer = null;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_customer_appointment';
        $this->schema = array(
            'id'             => array( 'format' => '%d' ),
            'customer_id'    => array( 'format' => '%d' ),
            'appointment_id' => array( 'format' => '%d' ),
            'notes'          => array( 'format' => '%s' ),
            'token'          => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

    /**
     * Save entity to database.
     * Generate token before saving.
     *
     * @return int|false
     */
    public function save() {
        // Generate new token if it is not set.
        if ( $this->get( 'token' ) == '' ) {
            $test = new self();
            do {
                $token = md5( uniqid( time(), true ) );
            }
            while ( $test->loadBy( array( 'token' => $token ) ) === true );

            $this->set( 'token', $token );
        }

        return parent::save();
    }
}