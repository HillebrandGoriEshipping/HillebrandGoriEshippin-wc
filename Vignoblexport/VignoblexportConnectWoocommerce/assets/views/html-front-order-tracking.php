<?php
/**
 * Front order tracking rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="VINW-order-tracking">
	<h2><?php esc_html_e( 'Package tracking', 'Vignoblexport' ); ?></h2>
	<?php
		require 'html-order-tracking.php';
	?>
</div>
