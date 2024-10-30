<?php

$pages = array('post.php', 'edit.php', 'post-new.php', 'page.php', 'page-new.php');
if (!in_array($GLOBALS['pagenow'], $pages)) {
	return;
}

add_action('init','WP_MM_scripts');
add_action('admin_menu', 'WP_mm_box');
add_action('save_post', 'WP_mm_save_post');
add_action('publish_post', 'WP_mm_save_post');

function WP_mm_save_post($i) {
	$i = wp_is_post_revision($i);
	if (!wp_verify_nonce($_POST['tern_wp_youtube_nonce'], plugin_basename(__FILE__)) or ! $i or ! current_user_can('edit_post', $i)) {
		return;
	}
	WP_mm_add_meta($i);
}

function WP_mm_box() {
	add_meta_box('mediominuto', 'Medio Minuto', 'WP_mm_meta', 'post', 'advanced','core');
}

function WP_MM_scripts() {
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery');
	wp_enqueue_script('mediominuto','http://api.mediominuto.es/files/mediominuto.js',array('jquery','thickbox'));
}

function WP_mm_meta() {
	global $post, $mm_fields, $getWP;
	$o = $getWP->getOption('mm_options',$mm_options);
	
	if( !$o['mm_api'] || $o['mm_api'] == '' )
	{
		echo '<div class="tern_errors"><p>Debes indicar tu clave API antes de usar este plugin.</p></div>';
		return;
	}
	// Generamos un nuevo slug para el video
	$y = new tern_curl();
	$f = $y->get(array(
		'url' => 'http://api.mediominuto.es/video/generateID/'.$o['mm_api'],
		'options' => array(
			'RETURNTRANSFER' => true,
			'FOLLOWLOCATION' => true,
			'SSL_VERIFYPEER' => false
		),
		'headers' => array(
			'Accept-Charset' => 'UTF-8'
		)
	));
	$d =  json_decode($f->body);
	if( $d->slug ) 
	{
		$slug = $d->slug;
	} else {
		
		echo '<div class="tern_errors"><p>No se ha podido generar un identificador para el video</p></div>';
		return;
	}
	
	$client_id = $o['mm_api'];
	$video_id = $slug;
	$javascript_code = "<script type='text/javascript'>"
	. "var insert_mm_video = function(client,id){"
	. "parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + \"[mediominuto id='\"+id+\"' client='\"+client+\"']\");"
	. "};"
	. "MM.init({"
	. "	mode: 'record',"
	. "client_id: '$client_id',"
	. "trigger: '#recorder_button',"
	. "video_id: '$video_id'"
	. "});"
	. "</script>";
	
	echo 'Para grabar un nuevo vídeo, debes pulsar el botón de grabación&nbsp;&nbsp;';
	echo $javascript_code;
	echo '&nbsp;&nbsp;<input OnClick="insert_mm_video(\''.$client_id.'\',\''.$slug.'\');" type="button" name="recorder_button" id="recorder_button" value="Graba tu video">';
	echo '<br><span style="font-size: 10px;">El vídeo asociado a este apunte tiene el ID: '.$slug.'</span><br>';
}
