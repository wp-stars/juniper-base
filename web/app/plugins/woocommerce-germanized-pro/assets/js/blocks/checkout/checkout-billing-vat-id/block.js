/**
 * External dependencies
 */
import { VatId } from '../vat-id/util';
import {useEffect} from "@wordpress/element";

const Block = ({
   children,
   checkoutExtensionData,
   extensions,
   cart
}) => {
	useEffect(() => {
		const blockElement = document.getElementsByClassName( 'wp-block-woocommerce-checkout-billing-address-block' )[0];
		const vatId = blockElement.getElementsByClassName( 'wc-gzd-billing-vat-id' )[0];
		let addressFormWrapper = blockElement.getElementsByClassName( 'wc-block-components-address-form' )[0];

		if ( ! addressFormWrapper ) {
			addressFormWrapper = blockElement.getElementsByClassName( 'wc-block-components-address-form-wrapper' )[0];
		}

		addressFormWrapper.appendChild( vatId );
	}, [] );

	return (
		<>
		<div className="wc-gzd-vat-id-element-placeholder"></div>
		<div className="wc-gzd-vat-id wc-gzd-billing-vat-id">
			<VatId
				cart={ cart }
				extensions={ extensions }
				checkoutExtensionData={ checkoutExtensionData }
				addressType={ 'billing' }
			/>
		</div>
		</>
	);
};

export default Block;
