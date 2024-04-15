<?php
/**
 * Setup wizard notice rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="VINW-notice VINW-info" style="text-align:center;">
	<a class="VINW-close-link VINW-hide-notice" rel="setup-wizard">x</a>
	<img src="<?php echo get_option('siteurl') ?>/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportConnectWoocommerce/assets/img/vignoblexport.png" alt="VignoblExport" >
	<h3><?php esc_html_e('VignoblExport Settings interface','Vignoblexport' ); ?></h3>
</div>
