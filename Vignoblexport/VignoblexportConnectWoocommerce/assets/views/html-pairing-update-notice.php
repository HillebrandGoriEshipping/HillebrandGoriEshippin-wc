<?php
/**
 * Pairing update notice rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="VINW-notice VINW-warning">
	<?php esc_html_e( 'Security alert: someone is trying to pair your site with VignoblExport. Was it you?', 'Vignoblexport' ); ?>
	<button class="button-secondary VINW-pairing-update-validate" VINW-pairing-update-validate="1" href="#"><?php esc_html_e( 'yes', 'Vignoblexport' ); ?></button>
	<button class="button-secondary VINW-pairing-update-validate" VINW-pairing-update-validate="0" href="#"><?php esc_html_e( 'no', 'Vignoblexport' ); ?></button>
</div>
