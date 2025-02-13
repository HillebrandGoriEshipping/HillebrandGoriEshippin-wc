<?php

/**
 * Shipping method rate line rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Controller;

if (! defined('ABSPATH')) {
	exit;
}

$state                = isset($pricing_item, $pricing_item['pricing']) ? $pricing_item['pricing'] : Controller::$rate;
$disabled             = Controller::$deactivated === $state;
$has_shipping_classes = count($shipping_classes) > 1;
?>

<tr class="pricing-item<?php echo $disabled ? ' disabled' : ''; ?>">
	<td class="sort"></td>
	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset($pricing_item, $pricing_item['price_from']) ? esc_html($pricing_item['price_from']) : null; ?>" name='pricing-items[<?php echo esc_html($i); ?>]["price-from"]' class="price-from">
	</td>
	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset($pricing_item, $pricing_item['price_to']) ? esc_html($pricing_item['price_to']) : null; ?>" name='pricing-items[<?php echo esc_html($i); ?>]["price-to"]' class="price-to">
	</td>
	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset($pricing_item, $pricing_item['weight_from']) ? esc_html($pricing_item['weight_from']) : null; ?>" name='pricing-items[<?php echo esc_html($i); ?>]["weight-from"]' class="weight-from">
	</td>
	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset($pricing_item, $pricing_item['weight_to']) ? esc_html($pricing_item['weight_to']) : null; ?>" name='pricing-items[<?php echo esc_html($i); ?>]["weight-to"]' class="weight-to">
	</td>

	<?php if ($has_shipping_classes) { ?>
		<td class="select">
			<select <?php echo $disabled ? 'disabled' : ''; ?> name='pricing-items[<?php echo esc_html($i); ?>]["shipping-class"][]' multiple="multiple" class="VINW-tail-select shipping-class">
				<?php
				$selected = isset($pricing_item, $pricing_item['shipping_class']) ? $pricing_item['shipping_class'] : false;
				foreach ($shipping_classes as $key => $name) {
					echo '<option value="' . esc_html($key) . '" ';
					if ((is_array($selected) && in_array($key, $selected, true)) || false === $selected) {
						echo 'selected';
					}
					echo '>' . esc_html($name) . '</option>';
				}
				?>
			</select>
		</td>
	<?php } ?>

	<td class="flat-rate">
		<input <?php echo $disabled ? 'disabled' : ''; ?>
			type="text"
			id="flat-rate-<?php echo esc_html($i); ?>"
			value="<?php echo isset($pricing_item, $pricing_item['flat_rate']) ? esc_html($pricing_item['flat_rate']) : null; ?>"
			name='pricing-items[<?php echo esc_html($i); ?>]["flat-rate"]'
			class="flat-rate">
	</td>

	<td class="state">
		<input type="checkbox"
			data-checked="<?php echo esc_html(Controller::$rate); ?>"
			data-unchecked="<?php echo esc_html(Controller::$deactivated); ?>"
			id="state-<?php echo esc_html($i); ?>"
			class="state VINW-change-state"
			name='pricing-items[<?php echo esc_html($i); ?>]["state"]'
			value="1"
			<?php echo checked(1, ! $disabled, false); ?> />
	</td>
	<td class="remove">
		<a <?php echo $disabled ? 'disabled' : ''; ?> class="VINW-remove-line dashicons-before dashicons-trash">
		</a>
	</td>
</tr>