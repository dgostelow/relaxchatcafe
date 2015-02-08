<?php

/**
 * Class AB_Customer
 */
class AB_Customer extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_customer';
        $this->schema = array(
            'id'      => array( 'format' => '%d' ),
            'name'    => array( 'format' => '%s', 'default' => '' ),
            'phone'   => array( 'format' => '%s', 'default' => '' ),
            'email'   => array( 'format' => '%s', 'default' => '' ),
            'notes'   => array( 'format' => '%s', 'default' => '' ),
        );
        parent::__construct();
    }
}