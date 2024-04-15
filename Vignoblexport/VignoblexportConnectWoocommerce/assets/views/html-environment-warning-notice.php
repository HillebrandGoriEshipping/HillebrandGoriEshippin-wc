<?php
/**
 * Environment warning notice rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="VINW-notice VINW-warning">
	<?php echo esc_html( $notice->message ); ?>
</div>
