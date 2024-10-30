<?php

add_action('admin_menu','WP_MM_menu');
add_action('admin_enqueue_scripts','WP_MM_styles');
add_action('delete_post','WP_MM_delete_posts');
add_action('all_admin_notices','WP_MM_errors');

function WP_MM_styles() {
	wp_enqueue_style('mm-admin',get_bloginfo('wpurl').'/wp-content/plugins/mediominuto/css/admin.css');
}

function WP_MM_menu() {
	if(function_exists('add_menu_page')) {
		add_menu_page('MedioMinuto','MedioMinuto',10,'mm-settings','WP_mm_settings',plugin_dir_url( __DIR__ ).'/img/logo.png');
		add_submenu_page('mm-settings','MedioMinuto','API Key',10,'mm-settings','WP_mm_settings',plugin_dir_url( __DIR__ ).'/img/logo.png');
		//add_submenu_page('mm-settings','MedioMinuto','Comentarios',10,'mm-comments','WP_mm_comments',plugin_dir_url( __DIR__ ).'/img/logo.png');
	}
}

function WP_MM_errors() {
	global $getWP,$mm_options;
	$o = $getWP->getOption('mm_options',$mm_options);
	if(empty($o['mm_api'])) {
		$getWP->addError('Recuerda que debes a&ntilde;adir tu clave API.');
	}
	
	$e = $getWP->renderErrors();
	if($e) {
		echo '<div class="tern_errors"><p>'.$e.'</p></div>';
	}
	
	$a = $getWP->renderAlerts();
	if($a) {
		echo '<div class="tern_alerts">'.$a.'</div>';
	}
}
