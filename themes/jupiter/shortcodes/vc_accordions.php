<?php
$title = $interval = $el_class = $output = '';

extract( shortcode_atts( array(
			'title' => '',
			'heading_title' => '',
			'interval' => 0,
			'style' => '',
			'container_bg_color' => '',
			'el_position' => '',
			'open_toggle' => '',
			'action_style' => 'accordion-style',
			'el_class' => ''
		), $atts ) );


$id = uniqid();

if ( !empty( $heading_title ) ) {
	$output .= '<h3 class="mk-shortcode mk-fancy-title pattern-style mk-shortcode-heading"><span>'.$heading_title.'</span></h3>';
}
$output .= '<div data-initialIndex="'.$open_toggle.'" id="mk-accordion-'.$id.'" class="mk-accordion mk-shortcode '.$action_style.' '.$style.' '.$el_class.'">';
$output .= wpb_js_remove_wpautop($content);
$output .= '</div>';


global $app_styles;
$app_styles .= '
 #mk-accordion-'.$id.' .mk-accordion-pane{
    background-color: '.$container_bg_color.';
}';


echo $output;
