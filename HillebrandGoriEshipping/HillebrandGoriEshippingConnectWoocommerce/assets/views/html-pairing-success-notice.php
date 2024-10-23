<?php

/**
 * Pairing success notice rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

if (! defined('ABSPATH')) {
	exit;
}

?>


<div class="VINW-notice VINW-success">
	<a class="VINW-close-link VINW-hide-notice" rel="pairing">x</a>
	<h2><?php esc_html_e('Congratulations, your shop is connected!', 'HillebrandGoriEshipping'); ?></h2>
	<p><?php esc_html_e('Finalize your settings to start shipping', 'HillebrandGoriEshipping'); ?></p>
	<p>
		<a href="<?php echo esc_url(admin_url('admin.php?page=Vignoblexport-settings')); ?>" class="button-primary" rel="pairing">
			<?php esc_html_e('Finalize the settings', 'HillebrandGoriEshipping'); ?>
		</a>
	</p>
</div>