<?php
if (!isset($_GET['page']) or ($_GET['page'] !== 'mm-settings' && $_GET['page'] !== 'mm-comments')) {
	return;
}

add_action('init', 'WP_mm_settings_actions');

function WP_mm_settings_actions() {
	global $getWP, $mm_options;
	$o = $getWP->getOption('mm_options', $mm_options);

	if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'mm_nonce') or ! current_user_can('manage_options')) {
		return;
	}

	switch ($_REQUEST['action']) {

		case 'update' :
			/*
			 * Test if API key is valid Key
			 */
			if (isset($_POST['mm_api'])) {
				$y = new tern_curl();
				$f = $y->get(array(
					'url' => 'http://api.mediominuto.es/user/' . $_POST['mm_api'],
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
				if ($d->is_error != 'false') {
					$getWP->addError('La clave API de MedioMinuto no parece válida');
				} else {
					$_SESSION['update_ok'] = TRUE;
				}
			}
			$getWP->updateOption('mm_options', $mm_options, 'mm_nonce');
			break;

		default :
			break;
	}
}

function WP_mm_settings() {
	global $getWP, $mm_options, $notice, $ternSel;
	$o = $getWP->getOption('mm_options', $mm_options);
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Opciones del plugin Medio Minuto</h2>
	<?php if (!empty($notice)) { ?><div id="notice" class="error"><p><?php echo $notice ?></p></div><?php } ?>
		<?php if ($_SESSION['update_ok'] == TRUE ) { ?>
			<div class="tern_ok"><p>Clave válida, modificación efectuada.</p></div>
			<p>Ya lo tienes activado, recuerda añadir tu dominio <b><?=$_SERVER["HTTP_HOST"]?></b> a la lista de dominios permitidos en tu <a href="http://panel.mediominuto.es">Panel de control</a> de MedioMinuto.</p>
			<p>Ya tienes activado en las páginas de entradas de tu blog el widget de MedioMinuto ... <a href="/wp-admin/post-new.php">compruébalo ya mismo</a> ;-)
		<?php } ?>
		<form method="post" action="">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="mm_api">Clave API de MedioMinuto:</label></th>
					<td>
						<input type="text" name="mm_api" class="regular-text" value="<?php echo $o['mm_api']; ?>" />
						<span class="setting-description">Pon aquí la clave API que te hemos proporcionado.</span>
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