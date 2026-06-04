const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const CopyPlugin    = require( 'copy-webpack-plugin' );

const copyRenderPhp = new CopyPlugin( {
	patterns: [
		{ from: 'src/blocks/posts-grid/render.php',       to: 'blocks/posts-grid/render.php' },
		{ from: 'src/blocks/posts-filter/render.php',     to: 'blocks/posts-filter/render.php' },
		{ from: 'src/blocks/posts-pagination/render.php', to: 'blocks/posts-pagination/render.php' },
	],
} );

if ( Array.isArray( defaultConfig ) ) {
	// WP_EXPERIMENTAL_MODULES=true: [scriptConfig, moduleConfig]
	const [ scriptConfig, moduleConfig ] = defaultConfig;
	module.exports = [
		{ ...scriptConfig, plugins: [ ...scriptConfig.plugins, copyRenderPhp ] },
		moduleConfig,
	];
} else {
	module.exports = {
		...defaultConfig,
		plugins: [ ...defaultConfig.plugins, copyRenderPhp ],
	};
}
