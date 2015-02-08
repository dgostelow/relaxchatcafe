<?php

extract( shortcode_atts( array(
			"icon" => '',
			"icon_size" => 'medium',
			"align" => 'left',
			'text_size' => '',
			'desc_size' => 14,
			'font_weight' => '',
			"icon_color" => '',
			"start" => 0,
			"stop" => 100,
			"speed" => 2000,
			"prefix" => '',
			"suffix" => '',
			"text" => '',
			"link" => '',
			"text_color" => '',
			'visibility' => '',
			"font_family" => '',
			"font_type" => '',
			'el_class' => '',
		), $atts ) );
$id = uniqid();

if ( !empty( $icon ) ) {
        $icon = (strpos($icon, 'mk-') !== FALSE) ? $icon : ( 'mk-'.$icon.'' );        
}

$text_size = ($text_size > 12) ? ';font-size:'.$text_size.'px' : '';
$font_weight = ($font_weight) ? ';font-weight:'.$font_weight.'' : '';


$output = '<div class="'.$el_class.' '.$visibility.'">';
$output .= '<div id="milestone-'.$id.'" class="mk-shortcode mk-milestone milestone-'.$icon_size.' '.$align.'-align" >';
$output .= ($link != '') ? '<a href="'.$link.'">' : '';
$output .= '<i style="color:'.$icon_color.'" class="'.$icon.'"></i>';
$output .= '<div class="milestone-top">';
$output .= !empty( $prefix ) ? ( '<span style="color:'.$text_color.$text_size.$font_weight.'" class="milestone-prefix">'.$prefix.'</span> ' ) : '';
$output .= '<span style="color:'.$text_color.$text_size.$font_weight.'" class="milestone-number" data-speed="'.$speed.'" data-stop="'.$stop.'">'.$start.'</span>';
$output .= !empty( $suffix ) ? ( ' <span style="color:'.$text_color.$text_size.$font_weight.'" class="milestone-suffix">'.$suffix.'</span>' ) : '';
$output .= (!empty($text)) ? ('<div style="color:'.$text_color.';font-size:'.$desc_size.'px" class="milestone-text">'.$text.'</div>') : '';
$output .= '</div>';
$output .= '<div class="clearboth"></div>';
$output .= ($link != '') ? '</a>' : '';
$output .= '</div></div>';

global $app_styles;
$app_styles .= '#milestone-'.$id.' .milestone-text:after{background:'.$text_color.';}';

$output .= mk_get_fontfamily( "#milestone-", $id, $font_family, $font_type );
echo $output;
