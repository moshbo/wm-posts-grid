<?php
defined( 'ABSPATH' ) || exit;

$cols_desktop   = isset( $attributes['columnsDesktop'] ) ? (int) $attributes['columnsDesktop'] : 3;
$cols_tablet    = isset( $attributes['columnsTablet'] )  ? (int) $attributes['columnsTablet']  : 2;
$cols_mobile    = isset( $attributes['columnsMobile'] )  ? (int) $attributes['columnsMobile']  : 1;
$posts_per_page = isset( $attributes['postsPerPage'] )   ? (int) $attributes['postsPerPage']   : 6;
$current_page   = max( 1, get_query_var( 'paged', 1 ) );

$cache_key = 'wm_pg_posts_' . md5( serialize( $attributes ) . '_p' . $current_page );
$cached    = get_transient( $cache_key );

if ( false !== $cached ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $cached;
	return;
}

$query = new WP_Query( [
	'post_type'      => 'wm_article',
	'posts_per_page' => $posts_per_page,
	'paged'          => $current_page,
	'post_status'    => 'publish',
] );

$wrapper_attrs = get_block_wrapper_attributes( [
	'class'            => 'wm-posts-grid',
	'data-block'       => 'wm-posts-grid',
	'data-posts-per-page' => (string) $posts_per_page,
	'style'            => "--wm-cols-desktop:{$cols_desktop};--wm-cols-tablet:{$cols_tablet};--wm-cols-mobile:{$cols_mobile}",
] );

ob_start();
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div
		class="wm-posts-grid__items"
		data-total-pages="<?php echo (int) $query->max_num_pages; ?>"
		data-current-page="<?php echo (int) $current_page; ?>"
	>
		<?php foreach ( $query->posts as $post ) : ?>
			<?php
			$thumbnail_html = get_the_post_thumbnail( $post->ID, 'medium_large', [ 'class' => 'wm-card__img' ] );
			$category_terms = get_the_terms( $post->ID, 'wm_category' );
			$category_name  = ( $category_terms && ! is_wp_error( $category_terms ) ) ? $category_terms[0]->name : '';
			$permalink      = get_permalink( $post->ID );
			$excerpt        = wp_trim_words( $post->post_excerpt ?: wp_strip_all_tags( $post->post_content ), 20, '...' );
			?>
			<article class="wm-card" data-post-id="<?php echo (int) $post->ID; ?>">
				<?php if ( $thumbnail_html ) : ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="wm-card__thumbnail" tabindex="-1" aria-hidden="true">
						<?php echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<div class="wm-card__body">
					<?php if ( $category_name ) : ?>
						<span class="wm-card__category"><?php echo esc_html( $category_name ); ?></span>
					<?php endif; ?>
					<h3 class="wm-card__title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
					</h3>
					<p class="wm-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<a href="<?php echo esc_url( $permalink ); ?>" class="wm-card__link">Read more</a>
				</div>
			</article>
		<?php endforeach; ?>

		<?php if ( ! $query->posts ) : ?>
			<p class="wm-posts-grid__empty">No articles found matching your selection.</p>
		<?php endif; ?>
	</div>

	<?php
	// Inner blocks (posts-pagination).
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $content;
	?>
</div>
<?php

$html = ob_get_clean();
set_transient( $cache_key, $html, HOUR_IN_SECONDS );
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $html;
