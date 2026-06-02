<?php
/**
 * Plugin Name: WM Posts Grid
 * Description: Custom articles grid with filtering and pagination Gutenberg blocks.
 * Version:     1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author:      Moshe Buaron
 * Text Domain: wm-posts-grid
 */

defined( 'ABSPATH' ) || exit;

define( 'WM_PG_VERSION',    '1.0.0' );
define( 'WM_PG_DIR',        plugin_dir_path( __FILE__ ) );
define( 'WM_PG_URL',        plugin_dir_url( __FILE__ ) );
define( 'WM_PG_BUILD_DIR',  WM_PG_DIR . 'build/' );

require_once WM_PG_DIR . 'includes/class-post-type.php';
require_once WM_PG_DIR . 'includes/class-activator.php';
require_once WM_PG_DIR . 'includes/class-deactivator.php';

register_activation_hook( __FILE__, [ 'WM_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'WM_Deactivator', 'deactivate' ] );

add_action( 'after_setup_theme', 'wm_pg_theme_support' );  // Ensure wide/full alignment works regardless of active theme.
add_action( 'init', [ 'WM_Post_Type', 'register' ] );     // Register CPT and taxonomies on every page load.
add_action( 'init', 'wm_pg_register_blocks' );             // Register Gutenberg blocks on every page load.
add_action( 'admin_notices', 'wm_pg_activation_notice' );  // Show success banner once after activation.
add_action( 'save_post_wm_article', 'wm_pg_clear_cache' ); // Clear grid cache when an article is saved.
add_action( 'deleted_post', 'wm_pg_clear_cache' );         // Clear grid cache when any post is deleted.

function wm_pg_theme_support(): void {
	add_theme_support( 'align-wide' );
}

function wm_pg_register_blocks(): void {
	register_block_type( WM_PG_BUILD_DIR . 'posts-grid' );       // Reads block.json → registers editor JS, frontend CSS/JS, render.php.
	register_block_type( WM_PG_BUILD_DIR . 'posts-filter' );     // Same for the filter block.
	register_block_type( WM_PG_BUILD_DIR . 'posts-pagination' ); // Same for the pagination inner block.

	// Inject REST API URL and nonce as a global JS variable before view scripts run.
	$script_data = [
		'apiUrl' => esc_url_raw( rest_url() ),      // Base REST API URL (e.g. https://site.com/wp-json/).
		'nonce'  => wp_create_nonce( 'wp_rest' ),   // Security token for authenticated REST requests.
	];

	wp_add_inline_script(
		'wm-posts-grid-view-script',                // Handle auto-generated from block name by register_block_type.
		'var wmPG = ' . wp_json_encode( $script_data ) . ';',
		'before'                                    // Inject before the script runs so wmPG is available immediately.
	);

	wp_add_inline_script(
		'wm-posts-filter-view-script',              // Same injection for the filter view script.
		'var wmPG = ' . wp_json_encode( $script_data ) . ';',
		'before'
	);
}

function wm_pg_activation_notice(): void {
	$data = get_transient( 'wm_pg_activation_notice' );
	if ( ! $data ) {
		return;
	}

	$posts_url = admin_url( 'edit.php?post_type=wm_article' );
	$page_url  = get_permalink( $data['page_id'] );

	printf(
		'<div class="notice notice-success is-dismissible"><p>WM Posts Grid activated successfully. <a href="%s">View articles</a> | <a href="%s" target="_blank">View demo page</a></p></div>',
		esc_url( $posts_url ),
		esc_url( $page_url )
	);

	delete_transient( 'wm_pg_activation_notice' );
}

function wm_pg_clear_cache(): void {
	global $wpdb;
	$wpdb->query(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wm_pg_posts_%'"
	);
}
