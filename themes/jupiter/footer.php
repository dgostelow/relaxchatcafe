<?php
global $mk_options;

$mk_footer_class = $show_footer_old = $show_footer = '';
$post_id = global_get_post_id();
if($post_id) {
  $show_footer_old = get_post_meta($post_id, '_footer', true );
  $show_footer = get_post_meta($post_id, '_template', true );

}

if($mk_options['footer_size'] == 'true') {
  $mk_footer_class .= 'mk-background-stretch';
}
if($mk_options['disable_footer'] == 'false' || ($show_footer_old == 'false' || $show_footer == 'no-footer' || $show_footer == 'no-header-footer' || $show_footer == 'no-header-title-footer' || $show_footer == 'no-footer-title')) {
  $mk_footer_class .= ' mk-footer-disable';
}

if($mk_options['footer_type'] == '2') {
  $mk_footer_class .= ' mk-footer-unfold';
}

$boxed_footer = (isset($mk_options['boxed_footer']) && !empty($mk_options['boxed_footer'])) ? $mk_options['boxed_footer'] : 'true';
$boxed_footer_css = ($boxed_footer == 'true') ? ' mk-grid' : ' fullwidth-footer';

?>
<section id="mk-footer" class="<?php echo $mk_footer_class; ?>">
<?php if($mk_options['disable_footer'] == 'true' && ($show_footer_old != 'false' && $show_footer != 'no-footer' && $show_footer != 'no-header-footer' && $show_footer != 'no-header-title-footer' && $show_footer != 'no-footer-title')) : ?>
<div class="footer-wrapper<?php echo $boxed_footer_css;?>">
<div class="mk-padding-wrapper">
<?php
$footer_column = $mk_options['footer_columns'];
if(is_numeric($footer_column)):
	switch ( $footer_column ):
		case 1:
		$class = '';
			break;
		case 2:
			$class = 'mk-col-1-2';
			break;
		case 3:
			$class = 'mk-col-1-3';
			break;
		case 4:
			$class = 'mk-col-1-4';
			break;
		case 5:
			$class = 'mk-col-1-5';
			break;
		case 6:
			$class = 'mk-col-1-6';
			break;
	endswitch;
	for( $i=1; $i<=$footer_column; $i++ ):
?>
<div class="<?php echo $class; ?>"><?php mk_sidebar_generator( 'get_footer_sidebar' )  ?></div>
<?php endfor;

else :

switch($footer_column):
		case 'third_sub_third':
?>
		<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		<div class="mk-col-2-3">
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
<?php
			break;
		case 'sub_third_third':
?>
		<div class="mk-col-2-3">
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
		<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
<?php
			break;
		case 'third_sub_fourth':
?>
		<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		<div class="mk-col-2-3 last">
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
<?php
			break;
		case 'sub_fourth_third':
?>
		<div class="mk-col-2-3">
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-4"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
		<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
<?php
			break;
		case 'half_sub_half':
?>
		<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		<div class="mk-col-1-2">
			<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
<?php
			break;
		case 'half_sub_third':
?>
		<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		<div class="mk-col-1-2">
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
<?php
			break;
		case 'sub_half_half':
?>
		<div class="mk-col-1-2">
			<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
		<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
<?php
			break;
		case 'sub_third_half':
?>
		<div class="mk-col-1-2">
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
			<div class="mk-col-1-3"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
		</div>
		<div class="mk-col-1-2"><?php mk_sidebar_generator( 'get_footer_sidebar' ); ?></div>
<?php
			break;
	endswitch;
endif;?>
<div class="clearboth"></div>
</div>
</div>
<?php endif;?>
<?php if ( $mk_options['disable_sub_footer'] == 'true' && ($show_footer_old != 'false' && $show_footer != 'no-footer' && $show_footer != 'no-header-footer' && $show_footer != 'no-header-title-footer' && $show_footer != 'no-footer-title')) { ?>
<div id="sub-footer">
	<div class="<?php echo $boxed_footer_css;?>">
		<?php if ( !empty( $mk_options['footer_logo'] ) ) {?>
		<div class="mk-footer-logo">
		    <a href="<?php echo home_url( '/' ); ?>" title="<?php bloginfo( 'name' ); ?>"><img alt="<?php bloginfo( 'name' ); ?>" src="<?php echo $mk_options['footer_logo']; ?>" /></a>
		</div>
		<?php } ?>

    	<span class="mk-footer-copyright"><?php echo stripslashes($mk_options['copyright']); ?></span>
    	<?php do_action('footer_menu'); ?>
	</div>
	<div class="clearboth"></div>
</div>
<?php } ?>

</section>





</div>
<?php


	do_action( 'side_dashboard');


	if($mk_options['custom_js']) :

	?>
		<script type="text/javascript">
		<?php echo stripslashes($mk_options['custom_js']); ?>
		</script>
	<?php

	endif;

	if($mk_options['analytics']){
		?>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '<?php echo stripslashes($mk_options['analytics']); ?>']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
	<?php } ?>

</div>

	<script type="text/javascript">

		<?php 
			global $app_styles, $app_json;
			$backslash_styles = str_replace('\\', '\\\\', $app_styles);
			$clean_styles = preg_replace('!\s+!', ' ', $backslash_styles);
			$clean_styles_w = str_replace("'", "\"", $clean_styles);
		?>	

		// Export settings to json boilerplate
		// $app_json[] = array(
		//   'name' => 'element_name',
		//   'params' => array(
		//       'attribute' => $value
		//     )
		// );

		php = {
		    hasAdminbar: <?php if(is_admin_bar_showing()) { echo 'true'; } else { echo 'false'; }; ?>,
		    json: (<?php echo json_encode($app_json); ?> != null) ? <?php echo json_encode($app_json); ?> : '',
		    styles: '<?php echo $clean_styles_w; ?>',
		    jsPath: '<?php echo THEME_JS; ?>'
	  	};

		var styleTag = document.createElement('style'),
			head = document.getElementsByTagName('head')[0];

		styleTag.type = 'text/css';
		styleTag.innerHTML = php.styles;
		head.appendChild(styleTag);
		
	</script>

	<a href="#" class="mk-go-top"><i class="mk-icon-chevron-up"></i></a>
	
	<?php
		do_action('quick_contact');
		do_action('full_screen_search_form');
	?>

		<script type="text/javascript">
			var require = {
				// Base path for application modules
				baseUrl: php.jsPath + '/require/modules',

				// Paths for extra modules. 
				// We can use folder names but we prefere do define shortcuts. 
				// Never add .js to the declaration
				paths: {
					// Library modules
					'async': '../lib/async',
					'fastdom': '../lib/fastdom',
					'jquery': '../lib/jquery',
					'window': '../lib/window',

					// Plugins 
					'plugin': '../plugins/',

					// Utils 
					'util': '../utils/',
				}
			};
		</script>
		<?php wp_footer(); ?>
	
		<?php 
			// This produces require() method for theme options
			$require_options = '';
			if($mk_options["disable_smoothscroll"] == "true") { 
				$require_options = "require(['util/smoothscroll'])";
			} 
		?>

		<?php
			global $app_modules;

			$modules = array();
			$params = array();


			$modulesLength = count($app_modules);

			if ($modulesLength > 0) {
				foreach ($app_modules as $key => $val) { 
					$modules[] = $val["name"]; 
					$params[] = $val["params"];
				};
			}

			$uniqueModules = array_unique($modules);
			function mk_strtoupper($m) {
			   	return strtoupper($m[1]);
			}
			function toCamel($str) {
				if(version_compare(phpversion(), '5.3', '>=')) {
					 
					return preg_replace_callback( '/-(.?)/', 'mk_strtoupper', $str);
				} else {
					return preg_replace("/\-(.)/e", "strtoupper('\\1')", $str);
				}
				
				
			}

			$require = "'".implode("','", $uniqueModules)."'";
			$inject = implode(',', $uniqueModules);
			$injectCamel = toCamel($inject);
		?>
		<?php if ($modulesLength > 0) { ?>
		<script type="text/javascript">
		// This produces require() method only for shortcodes that are currently in use.
		// Each shortcode is init() with set of it's own configuration based on the user choice. 
		// Dependencies and injected values are produced with unique lists, camelCased when needed.
			require([php.jsPath + '/require/app.js'], function() {

				<?php echo $require_options; ?>

				require([<?php echo $require; ?>], function(<?php echo $injectCamel; ?>) {
					<?php 
					for ($i = 0; $i < $modulesLength; $i++) {
						echo toCamel($modules[$i]) . ".init({";
							foreach ($params[$i] as $key => $val) {
								echo $key . ": '$val',";
							}
						echo "}); \n";
						} 
					?>
				});
			});
		</script>	
		<?php } ?>

	
	
</body>
</html>


