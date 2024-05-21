import { useMultilevelCheckoutDataContext } from "../data";
import { chevronLeft, Icon } from "@wordpress/icons";
import { _x, sprintf } from "@wordpress/i18n";

export const Frontend = ({
  children,
  className,
}) => {
    const {
        onPrevStep,
        getPrevStepTitle,
        getPrevStep,
    } = useMultilevelCheckoutDataContext();

    return (
        <div className="wc-gzdp-multilevel-checkout-step-footer woocommerce-gzdp-multilevel-checkout-submit">
            { getPrevStep() &&
                <a
                    href="#"
                    className="prev-step"
                    onClick={ onPrevStep }
                >
                    <Icon icon={ chevronLeft } />
                    { getPrevStepTitle() }
                </a>
            }
            { children }
        </div>
    );
};

export default Frontend;
