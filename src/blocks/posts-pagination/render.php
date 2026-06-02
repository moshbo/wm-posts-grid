<?php
defined( 'ABSPATH' ) || exit;

// Initial values — view.js updates these dynamically after filter/page changes.
$current_page = max( 1, get_query_var( 'paged', 1 ) );

$wrapper_attrs = get_block_wrapper_attributes( [
	'class'            => 'wm-posts-pagination',
	'data-block'       => 'wm-posts-pagination',
	'data-current-page' => (string) $current_page,
] );
?>
<nav <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="Articles pagination">
	<div class="wm-posts-pagination__pages"></div>
</nav>
