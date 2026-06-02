import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'wm-posts-pagination-editor' } );

	return (
		<div { ...blockProps }>
			<span>{ __( 'WM Posts Pagination', 'wm-posts-grid' ) }</span>
		</div>
	);
}
