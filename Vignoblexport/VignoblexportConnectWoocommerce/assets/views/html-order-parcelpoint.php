<?php
/**
 * Order tracking rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$weekday_initials = array(
	'MONDAY'    => substr( __( 'MONDAY', 'Vignoblexport' ), 0, 1 ),
	'TUESDAY'   => substr( __( 'TUESDAY', 'Vignoblexport' ), 0, 1 ),
	'WEDNESDAY' => substr( __( 'WEDNESDAY', 'Vignoblexport' ), 0, 1 ),
	'THURSDAY'  => substr( __( 'THURSDAY', 'Vignoblexport' ), 0, 1 ),
	'FRIDAY'    => substr( __( 'FRIDAY', 'Vignoblexport' ), 0, 1 ),
	'SATURDAY'  => substr( __( 'SATURDAY', 'Vignoblexport' ), 0, 1 ),
	'SUNDAY'    => substr( __( 'SUNDAY', 'Vignoblexport' ), 0, 1 ),
);
$has_opening_hours = false;
$has_address       = null !== $parcelpoint->name
	&& null !== $parcelpoint->address
	&& null !== $parcelpoint->zipcode
	&& null !== $parcelpoint->city
	&& null !== $parcelpoint->country;

if ( $has_opening_hours ) {
	$lines = array();
	foreach ( $parcelpoint->opening_hours as $index => $opening_hour ) {
		$am = '';
		$pm = '';
		if ( isset( $opening_hour->opening_periods[0] ) ) {
			$hours = $opening_hour->opening_periods[0];
			if ( strlen( $hours->open ) > 0 && strlen( $hours->close ) > 0 ) {
				$am = $hours->open . '-' . $hours->close;
			}
		}
		if ( isset( $opening_hour->opening_periods[1] ) ) {
			$hours = $opening_hour->opening_periods[1];
			if ( strlen( $hours->open ) > 0 && strlen( $hours->close ) > 0 ) {
				$pm = $hours->open . '-' . $hours->close;
			}
		}
		$line = $weekday_initials[ $opening_hour->weekday ] . ' ' . str_pad( $am, 11 ) . ' ' . str_pad( $pm, 11 );
		if ( 0 === $index % 2 ) {
			$line = '<span style="background-color: #d8d8d8;">' . $line . '</span>';
		}
		$lines[] = $line;
	}
	$opening_hours = implode( "\n", $lines );
}
if ( $has_address ) {
?>
	<h4><?php echo esc_html( __( 'Pickup point adress', 'Vignoblexport' ) ); ?></h4>
	<p>
		<?php echo esc_html( $parcelpoint->name ); ?><br/>
		<?php echo esc_html( $parcelpoint->address ); ?><br/>
		<?php echo esc_html( $parcelpoint->zipcode . ' ' . $parcelpoint->city . ' ' . $parcelpoint->country ); ?>
	</p>
	<?php
}

if ( true == false ) {
	?>
	<h4><?php echo esc_html( __( 'Opening hours', 'Vignoblexport' ) ); ?></h4>
	<pre style="background-color: inherit;"><?php echo wp_kses( $opening_hours, array( 'span' => array( 'style' => array() ) ) ); ?></pre>
<?php } ?>
