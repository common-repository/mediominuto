<?php

/*
  Plugin Name: MedioMinuto
  Plugin URI: http://www.mediominuto.es/wordpress-plugin
  Description: Plugin to implement mediominuto's API on your blog
  Author: Medio Minuto
  Version: 0.8
  Author URI:
  License:
  This software is licensed under the terms of the GNU General Public License v3
  as published by the Free Software Foundation. You should have received a copy of of
  the GNU General Public License along with this software. In the event that you
  have not, please visit: http://www.gnu.org/licenses/gpl-3.0.txt
 */

$mm_options = array(
	'mm_api' => '',
	'mm_comments' => '',
	'mm_comments_text' => '¡Envía una video-respuesta!'
);
$mm_fields = array(
	'MedioMinuto API:' => 'mm_api',
	'Permitir comentarios:' => 'mm_comments',
	'Texto de caja formulario:' => 'mm_comments_text',
);

/*
 * Includes
 */
require_once dirname(__FILE__) . '/class/wordpress.php';
require_once dirname(__FILE__) . '/class/forms.php';
require_once dirname(__FILE__) . '/class/curl.php';

require_once dirname(__FILE__) . '/core/admin.php';
require_once dirname(__FILE__) . '/core/settings.php';
require_once dirname(__FILE__) . '/core/comments.php';
require_once dirname(__FILE__) . '/core/meta.php';

function mediominuto_shortcode($atts) {
	extract(shortcode_atts(array(
		'id' => '',
		'client' => '',
		'target' => 'div'
					), $atts));
	// Register javascript library
	wp_enqueue_script('thickbox');
	wp_enqueue_script('jquery');
	wp_enqueue_script('mediominuto', 'http://api.mediominuto.es/files/mediominuto.js', array('jquery', 'thickbox'));

	// Warning: Use of EOF can break old PHP versions
	$result  = "<script type='text/javascript'>\n";
	$result .= "jQuery(document).ready(function() {\n";
	$result .= "	MM.init({\n";
	//$result .= "		debug: true,\n";
	$result .= "		mode: 'play',\n";
	$result .= "		client_id: '$client',\n";
	$result .= "		trigger: '#mm_element_$id',\n";
	$result .= "		video_id: '$id',\n";
	$result .= "		showPreview: true,\n";
	$result .= "	});\n";
	$result .= "});\n";
	$result .= "</script>\n";

	switch ($target) {
		case 'div':
			$result .= "<div id='mm_element_$id'></div>";
			break;
		case 'a':
			$result .= "<a href='#' id='mm_element_$id'>Pulsa aquí</div>";
			break;
		default:
			$result .= "<a href='#' id='mm_element_$id'>Pulsa aquí</div>";
			break;
	}
	return $result;
}

add_shortcode('mediominuto', 'mediominuto_shortcode');

add_action('init', 'MM_init', 0);

function MM_init() {
	global $getWP;
	$o = $getWP->getOption('mm_options', $mm_options);
}

/*
 * Show video on comments
 */

function MM_comment_record() {

	$o = get_option('mm_options');

	// Required option check on admin page
	if ($o['mm_comments'] == 1) {

		// Get video ID
		$y = new tern_curl();
		$f = $y->get(array(
			'url' => 'http://api.mediominuto.es/video/generateID/' . $o['mm_api'],
			'options' => array(
				'RETURNTRANSFER' => true,
				'FOLLOWLOCATION' => true,
				'SSL_VERIFYPEER' => false
			),
			'headers' => array(
				'Accept-Charset' => 'UTF-8'
			)
		));
		$d = json_decode($f->body);
		if (isset($d->slug)) {
			$camera_img = '<img src="' . plugin_dir_url(__FILE__) . '/img/video_camera-32.png">';
			$link_text = $o['mm_comments_text'];

			echo '<p><a href="#" class="mediominuto_link" id="comment_recorder_'.$d->slug.'">' . $camera_img . ' ' . $link_text . '</a></p>';
			echo '<input type="hidden" name="mm_video" id="mm_video" value="' . $d->slug . '">';
			echo "<script type='text/javascript'>"
			. "jQuery(document).ready(function() {"
			. "MM.init({"
			. "mode: 'record',"
			//. "debug:	true,"
			. "client_id: '".$o['mm_api']."',"
			. "trigger: '#comment_recorder_".$d->slug."',"
			. "video_id: '".$d->slug."',"
			. "});"
			. "});"
			. "</script>";
		}
	}
}

function MM_postId_comment($comment_id) {

//	$recCookie = $_COOKIE["recIdCookie"];
//    //$post_id = $_COOKIE["postIdCookie"];
//	
//	$comment = get_comment( $comment_id, ARRAY_A );
//	$post_id = $comment['comment_post_ID'];
//	
//	global $wpdb;
//	$table_name = $wpdb->prefix."vw_videocomrecordings";
//	
//	$sql="UPDATE $table_name SET postId = '$post_id', commentId = '$comment_id' WHERE id = '$recCookie' AND postId = '0'";
//	
//	$wpdb->query($sql);
	echo 'acabado';
}

add_action('comment_form_top', 'MM_comment_record');
add_action('comment_post', 'MM_postId_comment');
