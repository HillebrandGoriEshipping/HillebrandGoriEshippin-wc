<?php

/**
 * Configuration failure notice rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

if (! defined('ABSPATH')) {
	exit;
}

?>

<div class="VINW-notice VINW-warning">
	<?php esc_html_e('There was a problem initializing the Hillebrand Gori eShipping plugin. Please contact our support team.', 'HillebrandGoriEshipping'); ?>
</div>