<?php

/**
 * Admin order view modal tracking rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

if (! defined('ABSPATH')) {
	exit;
}
?>

<div class="VINW-order-tracking">
	<h2><?php esc_html_e('Tracking details', 'HillebrandGoriEshipping'); ?></h2>
	<?php
	require 'html-order-tracking.php';
	?>
</div>