<?php
/**
 * Pairing success notice rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>


<div class="VINW-notice VINW-success">
	<a class="VINW-close-link VINW-hide-notice" rel="pairing">x</a>
	<h2><?php esc_html_e( 'Congratulations, your shop is connected!', 'Vignoblexport' ); ?></h2>
	<p><?php esc_html_e( 'Finalize your settings to start shipping', 'Vignoblexport' ); ?></p>
	<p>
		<a  href="<?php echo esc_url( admin_url( 'admin.php?page=Vignoblexport-settings' ) ); ?>" class="button-primary" rel="pairing">
			<?php esc_html_e( 'Finalize the settings', 'Vignoblexport' ); ?>
		</a>
	</p>
</div>
