const { __ } = window.wp.i18n;
const { registerPlugin } = window.wp.plugins;
const { select } = window.wp.data;
const { ExperimentalOrderShippingPackages } = window.wc.blocksCheckout;

// hack, waiting for WooCommerce to build a customizable shuipping method block
//wc-blocks_render_blocks_frontend
window.addEventListener('wc-blocks_render_blocks_frontend', function() {
    document.querySelector('.wc-block-components-shipping-rates-control__package').remove();
});

const onClickedRate = (e) => {
    window.dispatchEvent(new Event('change'));
}

const MyCustomComponent = (props) => {
    const store = select( 'wc/store/cart' );
    const shippingPackages = store.getShippingRates();
    const shippingRates = shippingPackages[0].shipping_rates.map((r, i) => {
        const newRate = {
            ...r,
            key: i
        }

        r.meta_data.forEach(md => {
            r[md.key] = md.value;
        });

        return newRate;
    });


	return (<div>
        {shippingRates.map((rate) => (
          <div key={rate.key} onClick={(e) => onClickedRate(rate)}>
            <h4>{rate.name}</h4>
            <div>ETA : {rate.eta}</div>
            <p>Coût : {Number(rate.price / 100, 2)}</p>
          </div>
        ))}
      </div>);
};

const render = () => {
    
	return (
		<ExperimentalOrderShippingPackages>
            <MyCustomComponent />
		</ExperimentalOrderShippingPackages>
	);
};
registerPlugin( 'slot-and-fill-examples', {
	render,
	scope: 'woocommerce-checkout'
} );