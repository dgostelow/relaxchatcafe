<?php
 
class AB_NotificationReplacement {

    private $client_name = '';

    private $appointment_token = '';

    private $cancel_appointment = '';

    private $client_phone = '';

    private $client_email = '';

    private $client_notes = '';

    private $staff_name = '';

    private $appointment_time = '';

    private $service_name = '';

    private $service_price = 0;

    private $category_name = '';

    private $company_name = '';

    private $company_logo = '';

    private $company_address = '';

    private $company_phone = '';

    private $company_website = '';

    private $next_day_agenda = '';

    public function __construct() {
        $this->company_name    = get_option( 'ab_settings_company_name' );
        $this->company_logo    = get_option( 'ab_settings_company_logo_url' );
        $this->company_address = nl2br( get_option( 'ab_settings_company_address' ) );
        $this->company_phone   = get_option( 'ab_settings_company_phone' );
        $this->company_website = get_option( 'ab_settings_company_website' );
    }

    /**
     * @param $appointment_time
     */
    public function setAppointmentTime( $appointment_time ) {
        $this->appointment_time = $appointment_time;
    }

    /**
     * @param $client_name
     */
    public function setClientName( $client_name ) {
        $this->client_name = $client_name;
    }

    /**
     * @param $appointment_token
     */
    public function setAppointmentToken ( $appointment_token ) {
        $this->appointment_token = $appointment_token;
    }

    /**
     * @param $client_phone
     */
    public function setClientPhone( $client_phone ) {
        $this->client_phone = $client_phone;
    }

    /**
     * @param $client_email
     */
    public function setClientEmail( $client_email ) {
        $this->client_email = $client_email;
    }

    /**
     * @param $client_notes
     */
    public function setClientNotes( $client_notes ) {
        $this->client_notes = $client_notes;
    }

    /**
     * @param $service_name
     */
    public function setServiceName( $service_name ) {
        $this->service_name = $service_name;
    }

    /**
     * @param $service_price
     */
    public function setServicePrice( $service_price ) {
        $this->service_price = AB_CommonUtils::formatPrice( $service_price );
    }

    /**
     * @param $category_name
     */
    public function setCategoryName( $category_name ) {
        $this->category_name = $category_name;
    }

    /**
     * @param $agenda
     */
    public function setNextDayAgenda( $agenda ) {
        $this->next_day_agenda = $agenda;
    }

    /**
     * @param $staff_name
     */
    public function setStaffName( $staff_name ) {
        $this->staff_name = $staff_name;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function replaceSubject( $text ) {
        $replacement = array(
            '[[COMPANY_NAME]]'  => $this->company_name,
//            '[[TOMORROW_DATE]]' => ''
        );

        return str_replace( array_keys( $replacement ), array_values( $replacement ), $text );
    }

    /**
     * @param $text
     * @return mixed
     */
    public function replace( $text ) {
        $company_logo = isset( $this->company_logo ) ? $this->company_logo : '';
        if ( ! empty( $company_logo ) ) {
            $this->company_logo = '<img src="' . esc_attr( $company_logo ) . '" />';
        }

        $this->cancel_appointment = sprintf( '<a href="%1$s">%1$s</a>', get_site_url() . '/wp-admin/admin-ajax.php?action=ab_cancel_appointment&token=' . $this->appointment_token );

        $replacement = array(
            '[[STAFF_NAME]]'         => $this->staff_name,
            '[[CLIENT_NAME]]'        => $this->client_name,
            '[[CLIENT_PHONE]]'       => $this->client_phone,
            '[[CLIENT_EMAIL]]'       => $this->client_email,
            '[[CLIENT_NOTES]]'       => $this->client_notes,
            '[[APPOINTMENT_TIME]]'   => date_i18n( get_option( 'time_format' ), strtotime( $this->appointment_time ) ),
            '[[APPOINTMENT_DATE]]'   => date_i18n( get_option( 'date_format' ), strtotime( $this->appointment_time ) ),
            '[[SERVICE_NAME]]'       => $this->service_name,
            '[[SERVICE_PRICE]]'      => $this->service_price,
            '[[CATEGORY_NAME]]'      => $this->category_name,
            '[[COMPANY_NAME]]'       => $this->company_name,
            '[[COMPANY_LOGO]]'       => $this->company_logo,
            '[[COMPANY_ADDRESS]]'    => $this->company_address,
            '[[COMPANY_PHONE]]'      => $this->company_phone,
            '[[COMPANY_WEBSITE]]'    => $this->company_website,
            '[[NEXT_DAY_AGENDA]]'    => $this->next_day_agenda,
            '[[CANCEL_APPOINTMENT]]' => $this->cancel_appointment,
        );

        return str_replace( array_keys( $replacement ), array_values( $replacement ), $text );
    }
}
