<?php
defined( 'ABSPATH' ) || exit;

class WM_Post_Type {

	public static function register(): void {
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
			'rest_base'     => 'wm-articles',
			'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-media-document',
			'menu_position' => 5,
		] );

		register_taxonomy( 'wm_category', [ 'wm_article' ], [
			'labels'            => [
				'name'          => 'Article Categories',
				'singular_name' => 'Article Category',
			],
			'public'            => true,
			'show_in_rest'      => true,
			'rest_base'         => 'wm-categories',
			'hierarchical'      => true,
			'show_admin_column' => true,
		] );

		register_taxonomy( 'wm_tag', [ 'wm_article' ], [
			'labels'            => [
				'name'          => 'Article Tags',
				'singular_name' => 'Article Tag',
			],
			'public'            => true,
			'show_in_rest'      => true,
			'rest_base'         => 'wm-tags',
			'hierarchical'      => false,
			'show_admin_column' => true,
		] );
	}
}
