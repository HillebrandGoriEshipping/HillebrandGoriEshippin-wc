<?php

/**
 * Pairing failure notice rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

if (! defined('ABSPATH')) {
	exit;
}

?>

<div class="VINW-notice VINW-warning">
	<?php esc_html_e('Pairing with Hillebrand Gori eShipping API is not complete. Please check your WooCommerce connector in your Hillebrand Gori eShipping account for a more complete diagnostic.', 'HillebrandGoriEshipping'); ?>
</div>