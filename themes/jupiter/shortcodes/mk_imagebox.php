<?php

extract( shortcode_atts( array(
            'show_as' => '',
            'scroll_nav' => '',
            "animation_speed" => 700,
            "slideshow_speed" => 7000,
            "direction_nav" => "true",
            'column' => 4,
            'per_view' => 4,
            'padding' => 0,
			'el_class' => '',
		), $atts ) );


$id = uniqid();

if($show_as == 'column') {
    $output .= '<div id="mk-imagebox-' . $id . '" class="mk-imagebox-shortcode '.$show_as.'-style">';
    $column_class = '';
    switch($column) {
        case 1 :
        $column_class = 'imagebox-one-column';
        break;
        case 2 :
        $column_class = 'imagebox-two-column';
        break;
        case 3 :
        $column_class = 'imagebox-three-column';
        break;
        case 4 :
        $column_class = 'imagebox-four-column';
        break;
        case 5 :
        $column_class = 'imagebox-five-column';
        break;
        case 6 :
        $column_class = 'imagebox-six-column';
        continue;
    }
    $output .= '<div class="'.$column_class.'">'.wpb_js_remove_wpautop( $content, true ).'</div>';
    $output .= '<div class="clearboth"></div>';
    $output .= '</div>';
}else{
    $output .= '<div class="mk-imagebox-shortcode">';
    $output .= '<div id="mk-imagebox-' . $id . '" data-loop="true" data-direction="horizontal" data-slidesPerView="'.$per_view.'" data-mousewheelControl="false" data-freeModeFluid="true" data-slideshowSpeed="' . $slideshow_speed . '" data-animationSpeed="' . $animation_speed . '" class="swiper-container slide-style mk-swiper-slider ' . $el_class . '">';
    if($scroll_nav == 'true') {
        $output .= '<div class="swiper-navigation">';
        $output .= '<a class="mk-swiper-prev swiper-arrows"><i class="mk-jupiter-icon-arrow-left"></i></a>';
        $output .= '<a class="mk-swiper-next swiper-arrows"><i class="mk-jupiter-icon-arrow-right"></i></a>';
        $output .= '</div>';
    }
    $output .= '<div class="swiper-wrapper">';
    $output .= "\n\t\t\t".wpb_js_remove_wpautop( $content, true );
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
}

global $app_styles;
$app_styles .= '
#mk-imagebox-' . $id . ' .item-holder, #mk-imagebox-' . $id . ' .swiper-navigation{
    margin: 0 '.$padding.'px;
}';



echo $output;
