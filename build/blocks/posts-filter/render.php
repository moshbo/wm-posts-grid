<?php
defined( 'ABSPATH' ) || exit;

$categories = get_terms( [
	'taxonomy'   => 'wm_category',
	'hide_empty' => true,
	'orderby'    => 'name',
] );

$tags = get_terms( [
	'taxonomy'   => 'wm_tag',
	'hide_empty' => true,
	'orderby'    => 'name',
] );

if ( ( is_wp_error( $categories ) || empty( $categories ) ) && ( is_wp_error( $tags ) || empty( $tags ) ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes( [
	'class'      => 'wm-posts-filter',
	'data-block' => 'wm-posts-filter',
	'data-wp-interactive' => 'wm-posts-grid',
] );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
		<div class="wm-posts-filter__group">
			<span class="wm-posts-filter__label">Category</span>
			<div class="wm-posts-filter__pills" data-filter-type="categories">
				<?php foreach ( $categories as $term ) : ?>
					<button
						class="wm-posts-filter__pill"
						data-wp-interactive="wm-posts-grid"
						data-wp-context='<?php echo wp_json_encode(["termId" => $term->term_id, "type" => "categories"]); ?>'
						data-wp-on--click="actions.toggleFilter"
						data-wp-bind--aria-pressed="state.isPillActive"
					>
						<?php echo esc_html( $term->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) : ?>
		<div class="wm-posts-filter__group">
			<span class="wm-posts-filter__label">Tag</span>
			<div class="wm-posts-filter__pills" data-filter-type="tags">
				<?php foreach ( $tags as $term ) : ?>
					<button
						class="wm-posts-filter__pill"
						data-wp-interactive="wm-posts-grid"
						data-wp-context='<?php echo wp_json_encode(["termId" => $term->term_id, "type" => "tags"]); ?>'
						data-wp-on--click="actions.toggleFilter"
						data-wp-bind--aria-pressed="state.isPillActive"
					>
						<?php echo esc_html( $term->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<button
		class="wm-posts-filter__clear"
		data-wp-on--click="actions.clearFilters"
		data-wp-bind--hidden="!state.hasSelection">
		Clear filters
	</button>
</div>
