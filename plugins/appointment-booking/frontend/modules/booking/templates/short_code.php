<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-booking-form-<?php echo $form_id ?>" class="ab-booking-form" style="overflow: hidden"></div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#ab-booking-form-<?php echo $form_id ?>').appointmentBooking({
            ajaxurl: <?php echo json_encode( admin_url('admin-ajax.php') ) ?>,
            attributes: <?php echo $attributes ?>,
            last_step: <?php echo (int)$booking_finished  ?>,
            cancelled: <?php echo (int)$booking_cancelled  ?>,
            form_id: <?php echo json_encode( $form_id ) ?>,
            start_of_week: <?php echo json_encode( get_option( 'start_of_week' ) ) ?>,
            today_text: <?php echo json_encode( __( 'Today', 'ab' ) ) ?>,
            date_min: <?php echo json_encode( AB_BookingConfiguration::getDateMin() ) ?>
        });
    });
</script>