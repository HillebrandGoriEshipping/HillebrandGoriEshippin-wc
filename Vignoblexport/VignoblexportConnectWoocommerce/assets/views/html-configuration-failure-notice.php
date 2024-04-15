<?php
/**
 * Configuration failure notice rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="VINW-notice VINW-warning">
	<?php esc_html_e( 'There was a problem initializing the Vignoblexport plugin. Please contact our support team.', 'Vignoblexport' ); ?>
</div>
