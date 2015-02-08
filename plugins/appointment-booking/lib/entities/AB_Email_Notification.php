<?php

/**
 * Class AB_Email_Notification
 */
class AB_Email_Notification extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_email_notification';
        $this->schema = array(
            'id'          => array( 'format' => '%d' ),
            'type'        => array( 'format' => '%s' ),
            'customer_id' => array( 'format' => '%d' ),
            'staff_id'    => array( 'format' => '%d' ),
            'created'     => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

}