<?php
defined( 'ABSPATH' ) || exit;

class WM_Activator {

	private static array $post_ids       = [];
	private static array $attachment_ids = [];
	private static array $term_ids       = [];
	private static int   $demo_page_id   = 0;

	// Main entry point — runs once when the plugin is activated.
	public static function activate(): void {
		WM_Post_Type::register();
		flush_rewrite_rules();

		$content      = require WM_PG_DIR . 'includes/demo-content.php';
		$category_map = self::seed_terms( 'wm_category', $content['categories'] );
		$tag_map      = self::seed_terms( 'wm_tag', $content['tags'] );

		self::seed_posts( $content['posts'], $category_map, $tag_map );
		self::create_demo_page();

		update_option( 'wm_pg_post_ids',       self::$post_ids );
		update_option( 'wm_pg_attachment_ids', self::$attachment_ids );
		update_option( 'wm_pg_term_ids',       self::$term_ids );
		update_option( 'wm_pg_demo_page_id',   self::$demo_page_id );

		set_transient( 'wm_pg_activation_notice', [ 'page_id' => self::$demo_page_id ], 60 );
	}

	// Creates taxonomy terms and returns a name→ID map used when assigning terms to posts.
	private static function seed_terms( string $taxonomy, array $names ): array {
		$map = [];
		foreach ( $names as $name ) {
			$result = wp_insert_term( $name, $taxonomy );
			if ( is_wp_error( $result ) ) {
				$existing = get_term_by( 'name', $name, $taxonomy );
				if ( $existing ) {
					$map[ $name ] = $existing->term_id;
				}
			} else {
				$map[ $name ]     = $result['term_id'];
				self::$term_ids[] = [ 'id' => $result['term_id'], 'taxonomy' => $taxonomy ];
			}
		}
		return $map;
	}

	// Creates all demo articles, assigns their terms, and attaches a featured image to each.
	private static function seed_posts( array $posts, array $category_map, array $tag_map ): void {
		foreach ( $posts as $data ) {
			$post_id = wp_insert_post( [
				'post_title'   => $data['title'],
				'post_excerpt' => $data['excerpt'],
				'post_content' => $data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'wm_article',
				'post_date'    => $data['date'],
			] );

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			self::$post_ids[] = $post_id;

			$cat_ids = array_values( array_filter(
				array_map( fn( $name ) => $category_map[ $name ] ?? null, $data['categories'] )
			) );
			if ( $cat_ids ) {
				wp_set_object_terms( $post_id, $cat_ids, 'wm_category' );
			}

			$tag_ids = array_values( array_filter(
				array_map( fn( $name ) => $tag_map[ $name ] ?? null, $data['tags'] )
			) );
			if ( $tag_ids ) {
				wp_set_object_terms( $post_id, $tag_ids, 'wm_tag' );
			}

			$attachment_id = self::attach_remote_image( $data['image_seed'], $post_id );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
				self::$attachment_ids[] = $attachment_id;
			}
		}
	}

	// Downloads an image from picsum.photos and uploads it to the WP media library.
	private static function attach_remote_image( string $seed, int $post_id ): int|false {
		$url      = "https://picsum.photos/seed/{$seed}/1200/630";
		$response = wp_remote_get( $url, [ 'timeout' => 20 ] );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$image_data = wp_remote_retrieve_body( $response );
		$filename   = sanitize_file_name( $seed ) . '.jpg';
		$upload     = wp_upload_bits( $filename, null, $image_data );

		if ( ! empty( $upload['error'] ) ) {
			return false;
		}

		$attachment_id = wp_insert_attachment(
			[
				'post_title'     => sanitize_title( $seed ),
				'post_mime_type' => 'image/jpeg',
				'post_status'    => 'inherit',
			],
			$upload['file'],
			$post_id
		);

		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
		);

		return $attachment_id;
	}

	// Creates a published page with both blocks pre-inserted as block markup.
	private static function create_demo_page(): void {
		$block_content = '<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:wm/posts-filter /-->

<!-- wp:wm/posts-grid {"columnsDesktop":3,"columnsTablet":2,"columnsMobile":1,"postsPerPage":6} -->
<!-- wp:wm/posts-pagination /-->
<!-- /wp:wm/posts-grid --></div>
<!-- /wp:group -->';

		$page_id = wp_insert_post( [
			'post_title'   => 'WM Articles',
			'post_name'    => 'wm-articles',
			'post_content' => $block_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		] );

		if ( ! is_wp_error( $page_id ) ) {
			self::$demo_page_id = $page_id;
		}
	}
}
