<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit; // Only runs when WP itself triggers a plugin delete.

// Permanently delete all seeded articles (true = skip trash).
$post_ids = get_option( 'wm_pg_post_ids', [] );
foreach ( $post_ids as $id ) {
	wp_delete_post( $id, true );
}

// Permanently delete uploaded demo images and their physical files from uploads/.
$attachment_ids = get_option( 'wm_pg_attachment_ids', [] );
foreach ( $attachment_ids as $id ) {
	wp_delete_attachment( $id, true );
}

// Delete all seeded wm_category and wm_tag terms.
$term_ids = get_option( 'wm_pg_term_ids', [] );
foreach ( $term_ids as $item ) {
	wp_delete_term( $item['id'], $item['taxonomy'] ); // Requires both ID and taxonomy name.
}

// Delete the demo page created on activation.
$page_id = get_option( 'wm_pg_demo_page_id' );
if ( $page_id ) {
	wp_delete_post( $page_id, true );
}

// Remove the options that stored the seeded IDs.
delete_option( 'wm_pg_post_ids' );
delete_option( 'wm_pg_attachment_ids' );
delete_option( 'wm_pg_term_ids' );
delete_option( 'wm_pg_demo_page_id' );

// Delete all grid transients — WP has no built-in pattern delete, so raw SQL is used.
global $wpdb;
$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wm_pg_posts_%'"
);
