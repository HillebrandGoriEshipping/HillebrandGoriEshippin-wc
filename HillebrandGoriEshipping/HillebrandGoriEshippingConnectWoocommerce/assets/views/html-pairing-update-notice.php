<?php

/**
 * Pairing update notice rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

if (! defined('ABSPATH')) {
	exit;
}

?>

<div class="VINW-notice VINW-warning">
	<?php esc_html_e('Security alert: someone is trying to pair your site with Hillebrand Gori eShipping. Was it you?', 'HillebrandGoriEshipping'); ?>
	<button class="button-secondary VINW-pairing-update-validate" VINW-pairing-update-validate="1" href="#"><?php esc_html_e('yes', 'HillebrandGoriEshipping'); ?></button>
	<button class="button-secondary VINW-pairing-update-validate" VINW-pairing-update-validate="0" href="#"><?php esc_html_e('no', 'HillebrandGoriEshipping'); ?></button>
</div>