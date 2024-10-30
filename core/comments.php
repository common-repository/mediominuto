<?php
if (!isset($_GET['page']) or $_GET['page'] !== 'mm-comments') {
	return;
}

//add_action('init', 'WP_mm_comments_actions');

function WP_mm_comments() {
	global $getWP, $mm_options, $notice, $ternSel;
	$o = $getWP->getOption('mm_options', $mm_options);
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Video comentarios con Medio Minuto</h2>
		<?php if (!empty($notice)) { ?><div id="notice" class="error"><p><?php echo $notice ?></p></div><?php } ?>
		<form method="post" action="">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="mm_comments">Permitir respuesta en video en los comentarios:</label></th>
					<td>
						<input name="mm_comments" type="checkbox" value="1" <?= ($o['mm_comments'] == 1) ? 'checked' : ''; ?> />
					</td>
					<td>
						<span class="setting-description">Marca la opción para permitir comentarios en vídeo.</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mm_comments_text">Texto a mostrar:</label></th>
					<td>
						<input name="mm_comments_text" type="text" value="<?=$o['mm_comments_text']?>" />
					</td>
					<td>
						<span class="setting-description">Indica el texto que aparecerá en el formulario de comentarios.</span>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="submit" class="button-primary" value="Guardar cambios" /></p>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce('mm_nonce'); ?>" />
			<input type="hidden" name="_wp_http_referer" value="<?php wp_get_referer(); ?>" />
		</form>
	</div>
	<?php
}

function WP_mm_comments_actions() {
	global $getWP, $mm_options;
	$o = $getWP->getOption('mm_options', $mm_options);

	if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'mm_nonce') or ! current_user_can('manage_options')) {
		return;
	}

	switch ($_REQUEST['action']) {

		case 'update' :
			if( isset($_POST['mm_comments']))
			{
				$o['mm_comments'] = 1;
			} else {
				$o['mm_comments'] = 0;
			}
			$o['mm_comments_text'] = $_POST['mm_comments_text'];
			update_option( 'mm_options', $o );
			break;

		default :
			break;
	}
}
