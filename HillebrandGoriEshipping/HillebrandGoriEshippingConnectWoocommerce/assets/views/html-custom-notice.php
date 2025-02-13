<?php

/**
 * Custom notice rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

if (! defined('ABSPATH')) {
	exit;
}

?>
<div class="VINW-notice <?php echo esc_attr('VINW-' . $notice->status); ?>">
	<?php echo esc_html($notice->message); ?>
	<a class="button-secondary VINW-hide-notice" rel="<?php echo esc_attr($notice->key); ?>">
		<?php esc_html_e('Hide this notice', 'HillebrandGoriEshipping'); ?>
	</a>
</div>