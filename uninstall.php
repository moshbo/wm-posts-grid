<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$post_ids = get_option( 'wm_pg_post_ids', [] );
foreach ( $post_ids as $id ) {
	wp_delete_post( $id, true );
}

$attachment_ids = get_option( 'wm_pg_attachment_ids', [] );
foreach ( $attachment_ids as $id ) {
	wp_delete_attachment( $id, true );
}

$term_ids = get_option( 'wm_pg_term_ids', [] );
foreach ( $term_ids as $item ) {
	wp_delete_term( $item['id'], $item['taxonomy'] );
}

$page_id = get_option( 'wm_pg_demo_page_id' );
if ( $page_id ) {
	wp_delete_post( $page_id, true );
}

delete_option( 'wm_pg_post_ids' );
delete_option( 'wm_pg_attachment_ids' );
delete_option( 'wm_pg_term_ids' );
delete_option( 'wm_pg_demo_page_id' );

global $wpdb;
$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wm_pg_posts_%'"
);
