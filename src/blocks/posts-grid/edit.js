import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const TEMPLATE = [ [ 'wm/posts-pagination', {} ] ];

const DESKTOP_OPTIONS = [
	{ label: '2', value: 2 },
	{ label: '3', value: 3 },
	{ label: '4', value: 4 },
];

const TABLET_OPTIONS = [
	{ label: '1', value: 1 },
	{ label: '2', value: 2 },
	{ label: '3', value: 3 },
];

const MOBILE_OPTIONS = [
	{ label: '1', value: 1 },
	{ label: '2', value: 2 },
];

export default function Edit( { attributes, setAttributes } ) {
	const { columnsDesktop, columnsTablet, columnsMobile, postsPerPage } = attributes;
	const blockProps = useBlockProps( { className: 'wm-posts-grid-editor' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Grid Layout', 'wm-posts-grid' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Desktop Columns', 'wm-posts-grid' ) }
						value={ columnsDesktop }
						options={ DESKTOP_OPTIONS }
						onChange={ ( val ) => setAttributes( { columnsDesktop: parseInt( val ) } ) }
					/>
					<SelectControl
						label={ __( 'Tablet Columns', 'wm-posts-grid' ) }
						value={ columnsTablet }
						options={ TABLET_OPTIONS }
						onChange={ ( val ) => setAttributes( { columnsTablet: parseInt( val ) } ) }
					/>
					<SelectControl
						label={ __( 'Mobile Columns', 'wm-posts-grid' ) }
						value={ columnsMobile }
						options={ MOBILE_OPTIONS }
						onChange={ ( val ) => setAttributes( { columnsMobile: parseInt( val ) } ) }
					/>
					<RangeControl
						label={ __( 'Posts Per Page', 'wm-posts-grid' ) }
						value={ postsPerPage }
						min={ 3 }
						max={ 12 }
						step={ 1 }
						onChange={ ( val ) => setAttributes( { postsPerPage: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="wm-posts-grid-editor__preview">
					<p>{ __( 'WM Posts Grid', 'wm-posts-grid' ) }</p>
					<small>
						{ `${ columnsDesktop } cols desktop · ${ columnsTablet } cols tablet · ${ columnsMobile } col mobile · ${ postsPerPage } posts per page` }
					</small>
				</div>
				<InnerBlocks
					template={ TEMPLATE }
					allowedBlocks={ [ 'wm/posts-pagination' ] }
					templateLock="all"
				/>
			</div>
		</>
	);
}
