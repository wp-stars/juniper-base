/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { info, Icon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import sharedConfig from '../shared/config';
import edit from './edit';

const { ancestor, ...configuration } = sharedConfig;

const blockConfig = {
    ...configuration,
    apiVersion: 2,
    title: __( 'Unit products', 'woocommerce-germanized' ),
    description: __( 'Inserts the product\'s units.', 'woocommerce-germanized' ),
    usesContext: [ 'query', 'queryId', 'postId' ],
    icon: { src: <Icon
            icon={ info }
            className="wc-block-editor-components-block-icon"
        /> },

    supports: {
        ...sharedConfig.supports,
        ...( {
            __experimentalSelector:
                '.wp-block-woocommerce-gzd-product-unit-product .wc-gzd-block-components-product-unit-product',
        } )
    },
    edit,
};

registerBlockType( 'woocommerce-germanized/product-unit-product', blockConfig );
