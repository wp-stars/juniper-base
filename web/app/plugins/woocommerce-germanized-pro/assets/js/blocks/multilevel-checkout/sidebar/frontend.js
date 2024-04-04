import {useState, useEffect, useRef} from "@wordpress/element";
import { _x } from "@wordpress/i18n";
import { useSelect, select, dispatch } from '@wordpress/data';
import {
    chevronDown,
    chevronUp,
    Icon
} from '@wordpress/icons';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import FormattedMonetaryAmount from "@germanizedpro/base-components/formatted-monetary-amount";
import { getCurrencyFromPriceResponse } from "@germanizedpro/base-utils/currency";
import classnames from "classnames";

export const Frontend = ({
  children,
  className,
}) => {
    const [ open, setOpen ] = useState( false );
    const curRef = useRef( null );

    const {
        cartTotals,
    } = useSelect( ( select ) => {
        const store = select( CART_STORE_KEY );

        return {
            cartTotals: store.getCartTotals()
        }
    });

    useEffect(() => {
        if ( open ) {
            if ( curRef.current !== null ) {
                const panelButton = curRef.current.querySelector( '.wc-block-components-panel__button' );

                if ( panelButton ) {
                    const isExpanded = panelButton.getAttribute( 'aria-expanded' ) === 'true';

                    if ( ! isExpanded ) {
                        panelButton.click();
                    }
                }
            }
        }
    }, [ open, curRef ] );

    const totalPrice = cartTotals.total_price;

    return (
        <div
            className={ classnames({
                'wp-block-woocommerce-germanized-pro-multilevel-checkout-sidebar': true,
                'woocommerce-gzdp-multilevel-checkout-sidebar': true,
                'is-open': open
            } ) }
            ref={ curRef }
            >
            <div className="wp-block-woocommerce-germanized-pro-multilevel-checkout-sidebar-mobile-nav">
                <button className="multilevel-checkout-sidebar-nav-toggle" onClick={ () => setOpen( ! open ) }>
                    <span className="sidebar-mobile-summary-text">
                        { _x('Order Summary', 'multilevel-checkout', 'woocommerce-germanized-pro') }
                        <Icon icon={ open ? chevronUp : chevronDown } />
                    </span>
                    <span className="sidebar-mobile-summary-total">
                        <FormattedMonetaryAmount
                            currency={ getCurrencyFromPriceResponse( cartTotals ) }
                            value={ totalPrice }
                        />
                    </span>
                </button>
            </div>

            { children }
        </div>
    );
};

export default Frontend;
