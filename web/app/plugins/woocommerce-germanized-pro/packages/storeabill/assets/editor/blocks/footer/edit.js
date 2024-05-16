/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import {
	InnerBlocks,
	__experimentalBlock as Block,
} from '@wordpress/block-editor';
import {getAllowedBlockTypes} from "@storeabill/settings";

function FooterEdit( { attributes, className, clientId } ) {
	const hasInnerBlocks = useSelect(
		( select ) => {
			const { getBlock } = select( 'core/block-editor' );
			const block = getBlock( clientId );
			return !! ( block && block.innerBlocks.length );
		},
		[ clientId ]
	);

	const ALLOWED_BLOCKS = getAllowedBlockTypes().filter( blockName => blockName !== 'storeabill/header' );

	return (
		<div className={ className }>
			<div className="wp-block-group__inner-container sab-footer-container">
				<InnerBlocks
					allowedBlocks={ ALLOWED_BLOCKS }
					renderAppender={
						hasInnerBlocks
							? undefined
							: () => <InnerBlocks.ButtonBlockAppender />
					}
				/>
			</div>
		</div>
	);
}

export default FooterEdit;
