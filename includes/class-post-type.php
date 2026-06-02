<?php
defined( 'ABSPATH' ) || exit;

class WM_Post_Type {

	public static function register(): void {
		// Custom post type for articles — isolated from regular WP posts.
		// show_in_rest: true exposes it via REST API (/wp-json/wp/v2/wm-articles).
		register_post_type( 'wm_article', [
			'labels'       => [
				'name'               => 'Articles',
				'singular_name'      => 'Article',
				'add_new_item'       => 'Add New Article',
				'edit_item'          => 'Edit Article',
				'view_item'          => 'View Article',
				'search_items'       => 'Search Articles',
				'not_found'          => 'No articles found.',
			],
			'public'        => true,
			'show_in_rest'  => true,
			'rest_base'     => 'wm-articles',        // REST endpoint slug: /wp-json/wp/v2/wm-articles.
			'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-media-document',
			'menu_position' => 5,
		] );

		// Hierarchical taxonomy (like categories) — registered only for wm_article, not regular posts.
		// rest_base defines the filter param used in REST API requests: ?wm-categories=1,2
		register_taxonomy( 'wm_category', [ 'wm_article' ], [
			'labels'            => [
				'name'          => 'Article Categories',
				'singular_name' => 'Article Category',
			],
			'public'            => true,
			'show_in_rest'      => true,
			'rest_base'         => 'wm-categories',
			'hierarchical'      => true,             // Behaves like categories (parent/child support).
			'show_admin_column' => true,
		] );

		// Flat taxonomy (like tags) — registered only for wm_article, not regular posts.
		// rest_base defines the filter param used in REST API requests: ?wm-tags=3,4
		register_taxonomy( 'wm_tag', [ 'wm_article' ], [
			'labels'            => [
				'name'          => 'Article Tags',
				'singular_name' => 'Article Tag',
			],
			'public'            => true,
			'show_in_rest'      => true,
			'rest_base'         => 'wm-tags',
			'hierarchical'      => false,            // Flat — no parent/child like standard tags.
			'show_admin_column' => true,
		] );

		// Add thumbnail column to the admin list table.
		add_filter( 'manage_wm_article_posts_columns', [ self::class, 'add_thumbnail_column' ] );
		add_action( 'manage_wm_article_posts_custom_column', [ self::class, 'render_thumbnail_column' ], 10, 2 );
	}

	// Insert a thumbnail column before the title column.
	public static function add_thumbnail_column( array $columns ): array {
		$new = [];
		foreach ( $columns as $key => $value ) {
			if ( 'title' === $key ) {
				$new['wm_thumbnail'] = 'Image';
			}
			$new[ $key ] = $value;
		}
		return $new;
	}

	// Output the featured image for each row.
	public static function render_thumbnail_column( string $column, int $post_id ): void {
		if ( 'wm_thumbnail' !== $column ) {
			return;
		}
		$thumbnail = get_the_post_thumbnail( $post_id, [ 60, 60 ], [ 'style' => 'border-radius:4px;object-fit:cover;' ] );
		echo $thumbnail ?: '—';
	}
}
