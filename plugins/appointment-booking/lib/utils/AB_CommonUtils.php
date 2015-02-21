<?php

/**
 * Class AB_CommonUtils
 *
 */
class AB_CommonUtils {

    /**
     * Get e-mails of wp-admins
     *
     * @return array
     */
    public static function getAdminEmails() {
        return array_map(
            create_function( '$a', 'return $a->data->user_email;' ),
            get_users( 'role=administrator' )
        );
    } // getAdminEmails

    /**
     * Generates email's headers FROM: Sender Name < Sender E-mail >
     *
     * @return string
     */
    public static function getEmailHeaderFrom() {
        $from_name  = get_option( 'ab_settings_sender_name' );
        $from_email = get_option( 'ab_settings_sender_email' );
        $from = $from_name . ' <' . $from_email . '>';

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: '.$from . "\r\n";

        return $headers;
    } // getEmailHeaderFrom

    /**
     * Format price based on currency settings (Settings -> Payments).
     *
     * @param  string $price
     * @return string
     */
    public static function formatPrice( $price ) {
        $result = '';
        $price  = number_format_i18n( $price, 2 );
        switch (get_option('ab_paypal_currency')) {
            case 'AUD' :
                $result = 'A$' . $price;
                break;
            case 'BRL' :
                $result = 'R$ ' . $price;
                break;
            case 'CAD' :
                $result = 'C$' . $price;
                break;
            case 'RMB' :
                $result = $price . ' ¥';
                break;
            case 'CZK' :
                $result = $price . ' Kč';
                break;
            case 'DKK' :
                $result = $price . ' kr';
                break;
            case 'EUR' :
                $result = '€' . $price;
                break;
            case 'GTQ' :
                $result = 'Q' . $price;
                break;
            case 'HKD' :
                $result = $price . ' $';
                break;
            case 'HUF' :
                $result = $price . ' Ft';
                break;
            case 'IDR' :
                $result = $price . ' Rp';
                break;
            case 'INR' :
                $result = $price . ' ₹';
                break;
            case 'ILS' :
                $result = $price . ' ₪';
                break;
            case 'JPY' :
                $result = '¥' . $price;
                break;
            case 'KRW' :
                $result = $price . ' ₩';
                break;
            case 'MYR' :
                $result = $price . ' RM';
                break;
            case 'MXN' :
                $result = $price . ' $';
                break;
            case 'NOK' :
                $result = $price . ' kr';
                break;
            case 'NZD' :
                $result = $price . ' $';
                break;
            case 'PHP' :
                $result = $price . ' ₱';
                break;
            case 'PLN' :
                $result = $price . ' zł';
                break;
            case 'GBP' :
                $result = '£' . $price;
                break;
            case 'RON' :
                $result = $price . ' lei';
                break;
            case 'RUB' :
                $result = $price . ' руб.';
                break;
            case 'SGD' :
                $result = $price . ' $';
                break;
            case 'ZAR' :
                $result = 'R ' . $price;
                break;
            case 'SEK' :
                $result = $price . ' kr';
                break;
            case 'CHF' :
                $result = $price . ' CHF';
                break;
            case 'TWD' :
                $result = $price . ' NT$';
                break;
            case 'THB' :
                $result = $price . ' ฿';
                break;
            case 'TRY' :
                $result = $price . ' TL';
                break;
            case 'USD' :
                $result = '$' . $price;
                break;
        } // switch

        return $result;
    } // formatPrice

    /**
     * Format DateTime by User Settings
     *
     * @param string $dateTime
     *
     * @return string $dateTime
     */
    public static function getFormattedDateTime( $dateTime ) {
        if ( $dateTime ) {
            $dateTime = date_i18n( get_option( 'date_format' ), strtotime( $dateTime ) ) . ', ' .
                date_i18n( get_option( 'time_format' ), strtotime( $dateTime ) );
        }

        return $dateTime;
    } // getFormattedDateTime

    /**
     * @return string
     */
    public static function getCurrentPageURL() {
        return ($_SERVER['REQUEST_SCHEME'] ? $_SERVER['REQUEST_SCHEME'] : 'http') . "://".$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    }

} // AB_CommonUtils