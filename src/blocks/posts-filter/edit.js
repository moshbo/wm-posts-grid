import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'wm-posts-filter-editor' } );

	return (
		<div { ...blockProps }>
			<p className="wm-posts-filter-editor__label">
				{ __( 'WM Posts Filter — categories and tags will appear here on the frontend.', 'wm-posts-grid' ) }
			</p>
		</div>
	);
}
