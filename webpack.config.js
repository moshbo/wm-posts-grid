const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path          = require( 'path' );
const CopyPlugin    = require( 'copy-webpack-plugin' );

module.exports = {
	...defaultConfig,
	entry: {
		'posts-grid/index':             './src/blocks/posts-grid/index.js',
		'posts-grid/view':              './src/blocks/posts-grid/view.js',
		'posts-filter/index':           './src/blocks/posts-filter/index.js',
		'posts-filter/view':            './src/blocks/posts-filter/view.js',
		'posts-pagination/index':       './src/blocks/posts-pagination/index.js',
		'posts-pagination/view':        './src/blocks/posts-pagination/view.js',
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
	plugins: [
		// Filter out the default CopyPlugin that creates the duplicate build/blocks/ directory.
		...defaultConfig.plugins.filter( ( p ) => p.constructor.name !== 'CopyPlugin' ),
		new CopyPlugin( {
			patterns: [
				// Copy block.json files
				{ from: 'src/blocks/posts-grid/block.json',       to: 'posts-grid/block.json' },
				{ from: 'src/blocks/posts-filter/block.json',     to: 'posts-filter/block.json' },
				{ from: 'src/blocks/posts-pagination/block.json', to: 'posts-pagination/block.json' },
				// Copy render.php files
				{ from: 'src/blocks/posts-grid/render.php',       to: 'posts-grid/render.php' },
				{ from: 'src/blocks/posts-filter/render.php',     to: 'posts-filter/render.php' },
				{ from: 'src/blocks/posts-pagination/render.php', to: 'posts-pagination/render.php' },
			],
		} ),
	],
};
