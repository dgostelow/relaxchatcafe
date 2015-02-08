<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo add_query_arg( 'type', '_general' ) ?>" enctype="multipart/form-data" class="ab-staff-form">

    <?php if (isset($message_g)) : ?>
        <div id="message" style="margin: 0px!important;" class="updated below-h2">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p><?php echo $message_g ?></p>
        </div>
    <?php endif ?>

    <table class="form-horizontal">
        <tr>
            <td><?php _e('Time slot length','ab') ?></td>
            <td>
                <select name="ab_settings_time_slot_length" style="width: 200px;">
                    <?php
                    foreach ( array( 5, 10, 12, 15, 20, 30, 60 ) as $duration ) {
                        $duration_output = AB_Service::durationToString( $duration * 60 );
                        ?>
                        <option value="<?php echo $duration ?>" <?php selected( get_option( 'ab_settings_time_slot_length' ), $duration ); ?>>
                            <?php echo $duration_output ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php echo esc_attr( __( 'Select the time interval that will be used in frontend and backend, e.g. in calendar, second step of the booking process, while indicating the working hours, etc.', 'ab' ) ) ?>"
                    />
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e( 'Minimum time requirement prior to booking', 'ab' ) ?></label>
            </td>
            <td style="vertical-align: top">
                <select name="ab_settings_minimum_time_prior_booking" style="width: 200px;">
                    <option value="0" <?php echo !get_option( 'ab_settings_minimum_time_prior_booking' ) ? 'selected' : '' ?>><?php _e( 'Disabled', 'ab' ) ?></option>
                    <?php foreach ( array_merge(range(1, 12), array(24, 48)) as $hour): ?>
                        <option value="<?php echo $hour ?>" <?php echo get_option( 'ab_settings_minimum_time_prior_booking' ) == $hour ? 'selected' : '' ?>><?php echo $hour ?> h</option>
                    <?php endforeach ?>
                </select>
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php echo esc_attr( __( 'Set a minimum amount of time before the chosen appointment (for example, require the customer to book at least 1 hour before the appointment time).', 'ab' ) ) ?>"
                    />
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e( 'Display available time slots in client\'s time zone', 'ab' ) ?></label>
            </td>
            <td>
                <select name="ab_settings_use_client_time_zone" style="width: 200px;">
                    <?php foreach ( array( __( 'Disabled', 'ab' ) => '0', __( 'Enabled', 'ab' ) => '1' ) as $text => $mode ): ?>
                        <option value="<?php echo $mode ?>" <?php selected( get_option( 'ab_settings_use_client_time_zone' ), $mode ); ?> ><?php echo $text ?></option>
                    <?php endforeach ?>
                </select>
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php echo esc_attr( __( 'The value is taken from client’s browser.', 'ab' ) ) ?>"
                    />
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e( 'Cancel appointment page URL', 'ab' ) ?></label>
            </td>
            <td>
                <input type="text" name="ab_settings_cancel_page_url" value="<?php echo get_option( 'ab_settings_cancel_page_url' ) ?>" >
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php echo esc_attr( __( 'Insert the URL of the page that is shown to clients after they have cancelled their booking.', 'ab' ) ) ?>"
                    />
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <h4 style="float:left"><?php _e( 'Google Calendar', 'ab' ) ?></h4>
                <img style="float: left;margin-top: 8px;margin-left:15px;"
                     src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                     alt=""
                     class="ab-popover-ext"
                     data-ext_id="ab-gc-popover-ext"
                    />
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e( 'Client ID', 'ab' ) ?></label>
            </td>
            <td>
                <input type="text" name="ab_settings_google_client_id" value="<?php echo get_option( 'ab_settings_google_client_id' ) ?>" >
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php echo esc_attr( __( 'The client ID obtained from the Developers Console', 'ab' ) ) ?>"
                    />
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e( 'Client secret', 'ab' ) ?></label>
            </td>
            <td>
                <input type="text" name="ab_settings_google_client_secret" value="<?php echo get_option( 'ab_settings_google_client_secret' ) ?>" >
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php echo esc_attr( __( 'The client secret obtained from the Developers Console', 'ab' ) ) ?>"
                    />
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e( 'Redirect URI', 'ab' ) ?></label>
            </td>
            <td>
                <input type="text" readonly value="<?php echo AB_Google::generateRedirectURI() ?>" onclick="this.select();" style="cursor: pointer;">
            </td>
            <td>
                <img
                    src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                    alt=""
                    class="ab-popover"
                    data-content="<?php _e('Enter this URL as a redirect URI in the Developers Console', 'ab') ?>"
                    />
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="<?php _e( 'Save', 'ab' ) ?>" class="btn btn-info ab-update-button" />
                <button class="ab-reset-form" type="reset"><?php _e( ' Reset ', 'ab' ) ?></button>
            </td>
        </tr>
    </table>
</form>

<div id="ab-gc-popover-ext" style="display:none">
    <p><?php _e( 'To find your client ID and client secret, do the following:', 'ab' ) ?></p>
    <ol>
        <li><?php _e( 'Go to the <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.', 'ab' ) ?></li>
        <li><?php _e( 'Select a project, or create a new one.', 'ab' ) ?></li>
        <li><?php _e( 'In the sidebar on the left, expand <b>APIs & auth</b>. Next, click <b>APIs</b>. In the list of APIs, make sure the status is <b>ON</b> for the Google Calendar API.', 'ab' ) ?></li>
        <li><?php _e( 'In the sidebar on the left, select <b>Credentials</b>.', 'ab' ) ?></li>
        <li><?php _e( 'Create your project\'s OAuth 2.0 credentials by clicking <b>Create new Client ID</b>, selecting <b>Web application</b>, and providing the information needed to create the credentials. For <b>AUTHORIZED REDIRECT URIS</b> enter the <b>Redirect URI</b> found below on this page.', 'ab' ) ?></li>
        <li><?php _e( 'Look for the <b>Client ID</b> and <b>Client secret</b> in the table associated with each of your credentials.', 'ab' ) ?></li>
    </ol>
</div>