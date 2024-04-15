<?php
/**
 * Pairing failure notice rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="VINW-notice VINW-warning">
	<?php esc_html_e( 'Pairing with VignoblExport API is not complete. Please check your WooCommerce connector in your VignoblExport account for a more complete diagnostic.', 'Vignoblexport' ); ?>
</div>
