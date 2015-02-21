<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_UserBookingData {

    /**
     * @var int
     */
    private $form_id;

    /**
     * @var int
     */
    private $service_id;

    /**
     * @var array
     */
    private $staff_id = array();

    /**
     * @var string
     */
    private $date_from;

    /**
     * @var string
     */
    private $time_from;

    /**
     * @var string
     */
    private $time_to;

    /**
     * @var string
     */
    private $booked_datetime;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var int
     */
    private $client_time_offset = 0;

    /**
     * @var array
     */
    private $days = array();

    /**
     * @var int
     */
    private $customer_id;

    /**
     * @var string
     */
    private $coupon;

//    /**
//     * @var int
//     */
//    private $capacity;

    /**
     * @var string
     */
    private $custom_fields = '';

    public function __construct( $form_id ) {
        $this->form_id = $form_id;

        $prior_time = AB_BookingConfiguration::getMinimumTimePriorBooking();
        $this->date_from = date( 'Y-m-d', current_time( 'timestamp' ) + $prior_time );
    }

    public function hasData() {
        return isset($_SESSION[ 'appointment_booking' ][ $this->form_id ]);
    }

    public function load() {
        if ( isset($_SESSION[ 'appointment_booking' ][ $this->form_id ]) ) {
            $reflection = new ReflectionObject($this);
            foreach ( $reflection->getProperties() as $reflectionProperty ) {
                $field_name = $reflectionProperty->getName();
                if ( isset($_SESSION['appointment_booking'][ $this->form_id ][ $field_name ]) ) {
                    $this->$field_name = $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ];
                }
            }
        }
    }

    public function setData( $data ) {
        $reflection = new ReflectionObject($this);
        $default_properties = $reflection->getDefaultProperties();
        if ( !$this->hasData( $this->form_id ) ) {
            $_SESSION['appointment_booking'][ $this->form_id ] = array();
        }
        foreach ( $reflection->getProperties() as $reflectionProperty ) {
            $field_name = $reflectionProperty->getName();
            if ( isset ( $data[ $field_name ] ) ) {
                $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] = $data[ $field_name ];
            // overwrite to default property only if there are no property or it's empty
            } elseif ( !isset( $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] ) ||
                empty( $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] ) ) {
                    $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] = $default_properties[ $field_name ];
                }
        }
    }

    public function validate( $data ) {
        $reflection = new ReflectionObject($this);
        $validator = new AB_Validator();
        foreach ( $reflection->getProperties() as $reflectionProperty ) {
            $field_name = $reflectionProperty->getName();
            if ( isset($data[ $field_name ]) ) {
                switch ( $field_name ) {
                    case 'email':
                        $validator->validateEmail( $field_name, $data[ $field_name ], true );
                        break;
                    case 'phone':
                        $validator->validatePhone( $field_name, $data[ $field_name ], true );
                        break;
                    case 'date_from':
                    case 'time_from':
                    case 'time_to':
                    case 'booked_datetime':
                        $validator->validateDateTime( $field_name, $data[ $field_name ], true );
                        break;
                    case 'name':
                        $validator->validateString( $field_name, $data[ $field_name ], 255, true, true, 3 );
                        break;
                    case 'service_id':
                        $validator->validateNumber( $field_name, $data[ $field_name ] );
                        break;
                    case 'custom_fields':
                        $validator->validateCustomFields( $data[ $field_name ] );
                        break;
                }
            }
        }

        if ( isset( $data['time_from'] ) && isset( $data['time_to'] ) ) {
            $validator->validateTimeGt( 'time_from', $data['time_from'], $data['time_to'] );
        }

        return $validator->getErrors();
    }

    public function saveValidate(){
        $response = true;

        if (!get_option( 'ab_settings_pay_locally' )) { $response = false; }

        if ($this->getCoupon() && get_option('ab_settings_coupons')) {
            $coupon_exists = new AB_Coupon();
            $coupon_exists->loadBy(array('code' => $this->getCoupon(), 'discount' => 100));

            if ($coupon_exists->isLoaded()) {
                $response = true;
            }
        }

        if ($this->getServicePrice() == 0){
            $response = true;
        }

        if (AB_BookingConfiguration::isPaymentDisabled()){
            $response = true;
        }

        return $response;
    }

    /**
     * @return AB_Appointment
     */
    public function save() {
        /** @var wpdb $wpdb */
        global $wpdb;

        add_filter('wp_mail_from', create_function( '$content_type',
            'return get_option( \'ab_settings_sender_email\' ) == \'\' ?
                get_option( \'admin_email\' ) : get_option( \'ab_settings_sender_email\' );'
        ) );
        add_filter('wp_mail_from_name', create_function( '$name',
            'return get_option( \'ab_settings_sender_name\' ) == \'\' ?
                get_option( \'blogname\' ) : get_option( \'ab_settings_sender_name\' );'
        ) );

        // #11094: if customer with such name & e-mail exists, append new booking to him, otherwise - create new customer
        $customer_exists = $wpdb->get_row( $wpdb->prepare(
           'SELECT * FROM ab_customer WHERE name = %s AND email = %s', $this->name, $this->email
        ) );

        $customer =  new AB_Customer();
        if ( $customer_exists ) {
            $customer->set( 'id',    $customer_exists->id );
            $customer->set( 'name',  $customer_exists->name );
            $customer->set( 'email', $customer_exists->email );
            $customer->set( 'phone', $customer_exists->phone );
        } else {
            $customer->set( 'name',  $this->name );
            $customer->set( 'email', $this->email );
            $customer->set( 'phone', $this->phone );
            $customer->save();
        }
        $this->customer_id = $customer->get('id');

        $service = new AB_Service();
        $service->load( $this->service_id );

        /**
         * Get appointment, with same params.
         * If it is -> create connection to this appointment,
         * otherwise create appointment and connect customer to new appointment
         */
        $appointment = new AB_Appointment();
        $appointment->loadBy( array(
            'staff_id'   => $this->getStaffId(),
            'service_id' => $this->getServiceId(),
            'start_date' => $this->getBookedDatetime()
        ) );
        if ( $appointment->isLoaded() == false ) {
            $appointment->set('staff_id', $this->getStaffId() );
            $appointment->set('service_id', $this->service_id );
            $appointment->set('start_date', date('Y-m-d H:i:s', strtotime($this->booked_datetime)));

            $endDate = new DateTime($this->booked_datetime);
            $di = "+ {$service->get( 'duration' )} sec";
            $endDate->modify( $di );

            $appointment->set('end_date', $endDate->format('Y-m-d H:i:s'));
            $appointment->save();
        }

//        for ( $i = 1; $i <= $this->getCapacity(); $i++ ) {
            $customer_appointment = new AB_Customer_Appointment();
            $customer_appointment->set( 'appointment_id', $appointment->get( 'id' ) );
            $customer_appointment->set( 'customer_id', $customer->get( 'id' ) );
            $customer_appointment->set( 'custom_fields', $this->custom_fields );
            $customer_appointment->save();
//        }

        // Free coupon
        if ($this->getCoupon()){
            $coupon_exists = new AB_Coupon();
            $coupon_exists->loadBy(array('code' => $this->getCoupon(), 'discount' => 100));

            if ( $coupon_exists->isLoaded()) {
                $payment = new AB_Payment();
                $payment->set('coupon', $this->getCoupon() );
                $payment->set('total', '0.00' );
                $payment->set('type', 'coupon' );
                $payment->set('created', date('Y-m-d H:i:s') );
                $payment->set('customer_appointment_id', $customer_appointment->get('id') );
                $payment->save();

                $coupon_exists->set('used', 1);
                $coupon_exists->save();
            }
        }

        // Google Calendar.
        $appointment->handleGoogleCalendar();

        $appointment->sendEmailNotifications( $this->client_time_offset, $this->coupon );

        return $appointment;
    }

    public function clean() {
        unset( $_SESSION[ 'appointment_booking' ][ $this->form_id ] );
    }

    /**
     * @return string
     */
    public function getFormattedDateFrom() {
        return date_i18n( 'j F, Y', strtotime( $this->date_from ) );
    }

    /**
     * Get service name.
     *
     * @return null|string
     */
    public function getServiceName() {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare(
            'SELECT abs.title FROM `ab_service` as abs WHERE abs.id = %d', $this->getServiceId()
        ) );
    }

    /**
     * Get service price.
     *
     * @return null|int
     */
    public function getServicePrice()
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare(
            'SELECT price FROM ab_staff_service WHERE staff_id = %d AND service_id = %d',
            $this->getStaffId(),
            $this->getServiceId()
        ) );
    }

    /**
     * Get category name.
     *
     * @return string
     */
    public function getCategoryName() {
        /** @var wpdb $wpdb */
        global $wpdb;
        $result = $wpdb->get_var( $wpdb->prepare(
            'SELECT abc.name
                FROM `ab_category` as abc
                    LEFT JOIN `ab_service` as abs ON abs.category_id = abc.id
                WHERE abs.id = %d',
            $this->getServiceId()
        ) );

        return $result !== null ? $result : __( 'Uncategorized', 'ab' );
    }

    /**
     * @return int
     */
    public function getStaffId() {
        if ( count( $this->staff_id ) == 1 ) {
            return $this->staff_id[ 0 ];
        }
        return 0;
    }

    /**
     * Get staff name.
     *
     * @return string
     */
    public function getStaffName() {
        /** @var wpdb $wpdb */
        global $wpdb;

        $staff_id = $this->getStaffId();

        return $staff_id
            ? $wpdb->get_var( $wpdb->prepare( 'SELECT abs.full_name FROM `ab_staff` as abs WHERE abs.id = %d', $staff_id ) )
            : __( 'Any', 'ab' );
    }

    /**
     * @param $payment_id
     */
    public function setPaymentId( $payment_id ) {
        $_SESSION['appointment_booking'][ $this->form_id ]['payment_id'] = $payment_id;
    }

    /**
     * @return null|int
     */
    public function getPaymentId() {
        if ( isset($_SESSION['appointment_booking'][ $this->form_id ]['payment_id']) ) {
            return $_SESSION['appointment_booking'][ $this->form_id ]['payment_id'];
        }

        return null;
    }

    /**
     * @param $finished
     */
    public function setBookingFinished( $finished ) {
        $_SESSION['appointment_booking'][ $this->form_id ]['finished'] = $finished;
    }

    /**
     * @return bool|int
     */
    public function getBookingFinished() {
        if ( isset($_SESSION['appointment_booking'][ $this->form_id ]['finished']) ) {
            return $_SESSION['appointment_booking'][ $this->form_id ]['finished'];
        }

        return false;
    }

    /**
     * @param $cancelled
     */
    public function setBookingCancelled( $cancelled ) {
        $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ] = $cancelled;
    }

    /**
     * @param $error
     */
    public function setBookingPayPalError( $error ) {
        $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'paypal_error' ] = $error;
    }

    /**
     * @return bool
     */
    public function getBookingPayPalError() {
        if ( isset( $_SESSION[ 'appointment_booking' ][ $this->form_id ] ) && array_key_exists( 'paypal_error', $_SESSION[ 'appointment_booking' ][ $this->form_id ] ) ) {
            return $_SESSION['appointment_booking'][$this->form_id]['paypal_error'];
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getBookingCancelled() {
        if ( isset( $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ] ) ) {
            return $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ];
        }

        return false;
    }

    public function setClientTimeOffset($time_offset){
        $this->client_time_offset = $time_offset;
    }

    /**
     * Get form id.
     *
     * @return int
     */
    public function getFormId() {
        return $this->form_id;
    }

    /**
     * Get service id.
     *
     * @return int
     */
    public function getServiceId() {
        return $this->service_id;
    }

    /**
     * Get staff ids.
     *
     * @return array
     */
    public function getStaffIds() {
        return $this->staff_id;
    }

    /**
     * Get date-from.
     *
     * @return string
     */
    public function getDateFrom() {
        return $this->date_from;
    }

    /**
     * Get time-from.
     *
     * @return string
     */
    public function getTimeFrom() {
        if ( !$this->time_from ) {
            /** @var wpdb $wpdb */
            global $wpdb;
            $this->time_from = $wpdb->get_var(
                'SELECT SUBSTRING_INDEX(MIN(start_time), ":", 2) AS min_end_time
                    FROM ab_staff_schedule_item
                 WHERE start_time IS NOT NULL'
            );
        }

        return $this->time_from;
    }

    /**
     * Get time-to.
     *
     * @return string
     */
    public function getTimeTo() {
        if ( !$this->time_to ) {
            /** @var wpdb $wpdb */
            global $wpdb;
            $this->time_to = $wpdb->get_var(
                'SELECT SUBSTRING_INDEX(MAX(end_time), ":", 2) AS max_end_time
                    FROM ab_staff_schedule_item
                 WHERE end_time IS NOT NULL'
            );
        }

        return $this->time_to;
    }

    /**
     * @return string
     */
    public function getBookedDatetime() {
        return $this->booked_datetime;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

//    /**
//     * @return int
//     */
//    public function getCapacity() {
//        return $this->capacity;
//    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Get available days.
     *
     * @return array
     */
    public function getDays() {
        return $this->days;
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId(){
        return $this->customer_id;
    }

    /**
     * @return string
     */
    public function getCoupon() {
        return $this->coupon;
    }

    /**
     * @param $coupon
     */
    public function setCoupon($coupon){
        $_SESSION['appointment_booking'][ $this->form_id ]['coupon'] = $coupon;
        $this->coupon = $coupon;
    }
}