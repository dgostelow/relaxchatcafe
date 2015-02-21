<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
class AB_NotificationReplacement {

    private $data = array(
        'appointment_time'   => '',
        'appointment_token'  => '',
        'client_email'       => '',
        'client_name'        => '',
        'client_phone'       => '',
        'custom_fields'      => '',
        'service_name'       => '',
        'service_price'      => '',
        'staff_email'        => '',
        'staff_name'         => '',
        'staff_phone'        => '',
        'staff_photo'        => '',
        'cancel_appointment' => '',
        'category_name'      => '',
        'next_day_agenda'    => '',
    );

    public function set( $name, $value ) {
        if ( !array_key_exists( $name, $this->data ) ) {
            throw new InvalidArgumentException( sprintf( 'Trying to set unknown replacement "%s" for email notifications', $name ) );
        }

        $this->data[ $name ] = $value;
    }

    public function get( $name ) {
        if ( !array_key_exists( $name, $this->data ) ) {
            throw new InvalidArgumentException( sprintf( 'Trying to get unknown replacement "%s" for email notifications', $name ) );
        }

        return $this->data[ $name ];
    }

    /**
     * @param $text
     * @return mixed
     */
    public function replace( $text ) {
        $company_logo = '';
        if ( get_option( 'ab_settings_company_logo_url' ) != '' ) {
            $company_logo = sprintf(
                '<img src="%s" alt="%s" />',
                esc_attr( get_option( 'ab_settings_company_logo_url' ) ),
                esc_attr( get_option( 'ab_settings_company_name' ) )
            );
        }

        $staff_photo = '';
        if ( $this->data[ 'staff_photo' ] != '' ) {
            $staff_photo = sprintf(
                '<img src="%s" alt="%s" />',
                esc_attr( $this->data[ 'staff_photo' ] ),
                esc_attr( $this->data[ 'staff_name' ] )
            );
        }

        $cancel_appointment = sprintf(
            '<a href="%1$s">%1$s</a>',
            admin_url( 'admin-ajax.php' ) . '?action=ab_cancel_appointment&token=' . $this->data[ 'appointment_token' ]
        );

        $replacement = array(
            '[[APPOINTMENT_TIME]]'   => date_i18n( get_option( 'time_format' ), strtotime( $this->data[ 'appointment_time' ] ) ),
            '[[APPOINTMENT_DATE]]'   => date_i18n( get_option( 'date_format' ), strtotime( $this->data[ 'appointment_time' ] ) ),
            '[[CUSTOM_FIELDS]]'      => $this->data[ 'custom_fields' ],
            '[[CLIENT_NAME]]'        => $this->data[ 'client_name' ],
            '[[CLIENT_PHONE]]'       => $this->data[ 'client_phone' ],
            '[[CLIENT_EMAIL]]'       => $this->data[ 'client_email' ],
            '[[SERVICE_NAME]]'       => $this->data[ 'service_name' ],
            '[[SERVICE_PRICE]]'      => $this->data[ 'service_price' ],
            '[[STAFF_EMAIL]]'        => $this->data[ 'staff_email' ],
            '[[STAFF_NAME]]'         => $this->data[ 'staff_name' ],
            '[[STAFF_PHONE]]'        => $this->data[ 'staff_phone' ],
            '[[STAFF_PHOTO]]'        => $staff_photo,
            '[[CANCEL_APPOINTMENT]]' => $cancel_appointment,
            '[[CATEGORY_NAME]]'      => $this->data[ 'category_name' ],
            '[[COMPANY_ADDRESS]]'    => nl2br( get_option( 'ab_settings_company_address' ) ),
            '[[COMPANY_LOGO]]'       => $company_logo,
            '[[COMPANY_NAME]]'       => get_option( 'ab_settings_company_name' ),
            '[[COMPANY_PHONE]]'      => get_option( 'ab_settings_company_phone' ),
            '[[COMPANY_WEBSITE]]'    => get_option( 'ab_settings_company_website' ),
            '[[NEXT_DAY_AGENDA]]'    => $this->data[ 'next_day_agenda' ],
            '[[TOMORROW_DATE]]'      => date_i18n( get_option( 'date_format' ), strtotime( $this->data[ 'appointment_time' ] ) ),
        );

        return str_replace( array_keys( $replacement ), array_values( $replacement ), $text );
    }
}
