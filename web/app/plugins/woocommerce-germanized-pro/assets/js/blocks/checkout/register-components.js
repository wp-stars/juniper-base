/**
 * External dependencies
 */
import { registerCheckoutBlock } from '@woocommerce/blocks-checkout';
import { lazy } from '@wordpress/element';

import metadata from './component-metadata';

registerCheckoutBlock({
    metadata: metadata.CHECKOUT_BILLING_VAT_ID,
    component: lazy(
        () =>
            import(
                /* webpackChunkName: "checkout-blocks/checkout-billing-vat-id" */ './checkout-billing-vat-id/frontend'
                )
    ),
});

registerCheckoutBlock({
    metadata: metadata.CHECKOUT_SHIPPING_VAT_ID,
    component: lazy(
        () =>
            import(
                /* webpackChunkName: "checkout-blocks/checkout-shipping-vat-id" */ './checkout-shipping-vat-id/frontend'
                )
    ),
});