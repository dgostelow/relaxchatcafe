<?php

/**
 * Class AB_StaffService
 */
class AB_StaffService extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_staff_service';
        $this->schema = array(
            'id'            => array( 'format' => '%d' ),
            'staff_id'      => array( 'format' => '%d' ),
            'service_id'    => array( 'format' => '%d' ),
            'price'         => array( 'format' => '%.2f', 'default' => '0' ),
            'capacity'      => array( 'format' => '%d', 'default' => '1' ),
        );
        parent::__construct();
    }
}
