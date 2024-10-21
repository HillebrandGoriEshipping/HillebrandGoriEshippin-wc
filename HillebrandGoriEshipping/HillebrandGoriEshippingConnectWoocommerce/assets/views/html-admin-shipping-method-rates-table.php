<?php

/**
 * Shipping method rates table rendering
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Assets\Views
 */

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Misc_Util;

if (! defined('ABSPATH')) {
	exit;
}
$has_shipping_classes = count($shipping_classes) > 1;
?>

<table class="form-table" class="VINW-shipping-method-info">
	<thead>
		<th class="VINW-pricing-header">
			<b><?php esc_html_e('Pricing rules', 'HillebrandGoriEshipping'); ?></b>
			<p>
				<?php esc_html_e('your clients in the checkout page. The rules are prioritized from top to bottom. If no rules is applicable, the shipping method won\'t be diplayed.', 'HillebrandGoriEshipping'); ?>
				<br />
				<?php
				if (null !== $help_shipping_method_link) {
					/* translators: %1$1s: link start %2$2s: link end*/
					echo sprintf(esc_html__('Need some help? Just follow the instructions on %1$sthis article%2$s.', 'HillebrandGoriEshipping'), '<a href="' . esc_url($help_shipping_method_link) . '" target="_blank">', '</a>');
				}
				?>
				<?php
				if (null !== $shipping_rates_link) {
					/* translators: %1$1s: link start %2$2s: link end*/
					echo sprintf(esc_html__('In need of some advice about shipping costs? Follow %1$sthis link%2$s.', 'HillebrandGoriEshipping'), '<a href="' . esc_url($shipping_rates_link) . '" target="_blank">', '</a>');
				}
				?>
				<br />
				<span class="description light"><b><?php esc_html_e('Hillebrand Gori eShipping Tips', 'HillebrandGoriEshipping'); ?></b> :
					<?php
					/* translators: %1$1s: link start %2$2s: link end*/
					$message = __('Once your rules are set up here, use our %1$sshipping rule%2$s to automatize the selection of a carrier offer, the subscription to our AXA insurance and stop importing the orders that are not to be processed through Hillebrand Gori eShipping (e.g. If the shipping method is « Store pickup »)', 'HillebrandGoriEshipping');
					if (null !== $shipping_rules_link) {
						echo sprintf(esc_html($message), '<a href="' . esc_url($shipping_rules_link) . '" target="_blank">', '</a>');
					} else {
						echo sprintf(esc_html($message), '', '');
					}
					$shipping_rules_link
					?>
				</span>
			</p>
		</th>
	</thead>
</table>
<table id="VINW-rates-table" class="wc_input_table sortable widefat" data-default-shipping-class="<?php echo esc_html(array_keys($shipping_classes)[0]); ?>">
	<thead>
		<tr>
			<th rowspan="2" class="sort">&nbsp;</th>
			<th colspan="2" class="VINW-center">
				<?php echo esc_html__('Cart price Excluding Tax', 'HillebrandGoriEshipping') . ' (' . esc_html__(get_woocommerce_currency_symbol()) . ') '; ?>
			</th>
			<th colspan="2" class="VINW-center"><?php echo esc_html__('Number of bottles', 'HillebrandGoriEshipping'); ?></th>
			<th rowspan="2" class="w11 VINW-center">
				<?php
				echo '<span class="mr2">' . esc_html__('Price displayed ex-Tax', 'HillebrandGoriEshipping') . ' (' . esc_html(get_woocommerce_currency_symbol()) . ')</span>';
				$tooltip_html =  __('If you wish to offer the shipping for free, put 0.', 'HillebrandGoriEshipping') . "<br/>";
				$tooltip_html .= __('If you\'ve set up a shipping tax, it will be applied to this price for your client.', 'HillebrandGoriEshipping');
				Misc_Util::echo_tooltip($tooltip_html);
				?>
			</th>
			<th rowspan="2" class="w11 VINW-center">
				<?php
				echo '<span class="mr2">' . esc_html__('Status', 'HillebrandGoriEshipping') . '</span>';
				?>
			</th>
			<th rowspan="2"></th>
		</tr>
		<tr>
			<th class="VINW-center"><?php esc_html_e('From', 'HillebrandGoriEshipping'); ?> (≥)</th>
			<th class="VINW-center"><?php esc_html_e('To', 'HillebrandGoriEshipping'); ?> (<)< /th>
			<th class="VINW-center"><?php esc_html_e('From', 'HillebrandGoriEshipping'); ?> (≥)</th>
			<th class="VINW-center"><?php esc_html_e('To', 'HillebrandGoriEshipping'); ?> (<)< /th>
		</tr>
	</thead>
	<tbody class="ui-sortable">
		<?php
		if (isset($pricing_items) && is_array($pricing_items)) {
			$i = 0;
			foreach ($pricing_items as $pricing_item) {
				include 'html-admin-shipping-method-rate.php';
				$i++;
			}
		}
		?>
	</tbody>
</table>

<button class="VINW-add-rate-line">
	<i class="dashicons dashicons-plus-alt"></i>
	<?php esc_html_e('Add rule', 'HillebrandGoriEshipping'); ?>
</button>
<input type="hidden" name="save" value="1">