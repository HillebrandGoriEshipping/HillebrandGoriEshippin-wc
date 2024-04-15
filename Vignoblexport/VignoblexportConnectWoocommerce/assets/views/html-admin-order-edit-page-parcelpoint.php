<?php
	/**
	 * Admin order edit page tracking rendering
	 *
	 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	$network       = $parcelpoint->network;
	$networks_name = isset( $parcelpoint_networks->$network )
	? implode( ', ', $parcelpoint_networks->$network ) : null;
?>
<div class="VINW-order-parcelpoint">
	<p>
		<?php
		echo wp_kses( sprintf(
			/* translators: %1$s : parcelpoint code, %2$s : parcelpoint network name */
			__( 'Your client chose the pickup point %1$s from %2$s.', 'Vignoblexport' ),
			'<b>' . $parcelpoint->code . '</b>',
			$networks_name
		), array( 'b' => array() ) );
		?>
	</p>
	<?php
		require 'html-order-parcelpoint.php';
	?>
</div>
