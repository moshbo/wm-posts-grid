document.addEventListener( 'DOMContentLoaded', () => {
	const grid = document.querySelector( '[data-block="wm-posts-grid"]' );
	if ( ! grid ) return;

	let activeFilters = { categories: [], tags: [] };

	document.addEventListener( 'wm:filter-change', ( e ) => {
		activeFilters = { categories: e.detail.categories, tags: e.detail.tags };
		fetchPosts( 1 );
	} );

	document.addEventListener( 'wm:page-change', ( e ) => {
		fetchPosts( e.detail.page );
	} );

	async function fetchPosts( page ) {
		const postsPerPage = parseInt( grid.dataset.postsPerPage ) || 6;
		const params       = new URLSearchParams( {
			per_page: postsPerPage,
			page,
			_embed:   'wp:featuredmedia,wp:term',
		} );

		if ( activeFilters.categories.length ) {
			params.set( 'wm-categories', activeFilters.categories.join( ',' ) );
		}
		if ( activeFilters.tags.length ) {
			params.set( 'wm-tags', activeFilters.tags.join( ',' ) );
		}

		const itemsEl = grid.querySelector( '.wm-posts-grid__items' );
		itemsEl.classList.add( 'is-loading' );

		try {
			const response = await fetch(
				`${ wmPG.apiUrl }wp/v2/wm-articles?${ params.toString() }`,
				{ headers: { 'X-WP-Nonce': wmPG.nonce } }
			);

			if ( ! response.ok ) throw new Error( `HTTP ${ response.status }` );

			const posts      = await response.json();
			const totalPages = parseInt( response.headers.get( 'X-WP-TotalPages' ) ) || 1;

			itemsEl.innerHTML = posts.length
				? posts.map( renderCard ).join( '' )
				: '<p class="wm-posts-grid__empty">No articles found matching your selection.</p>';

			itemsEl.dataset.totalPages  = totalPages;
			itemsEl.dataset.currentPage = page;

			document.dispatchEvent( new CustomEvent( 'wm:pagination-update', {
				detail: { totalPages, currentPage: page },
			} ) );

			grid.dataset.activeFilters = JSON.stringify( activeFilters );
		} catch ( err ) {
			// eslint-disable-next-line no-console
			console.error( 'WM Posts Grid: failed to fetch posts', err );
		} finally {
			itemsEl.classList.remove( 'is-loading' );
		}
	}
} );

function renderCard( post ) {
	const media      = post._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ];
	const imageUrl   = media?.media_details?.sizes?.medium_large?.source_url || media?.source_url || '';
	const imageAlt   = media?.alt_text || post.title.rendered;
	const terms      = post._embedded?.[ 'wp:term' ] || [];
	const categories = terms[ 0 ] || [];
	const category   = categories[ 0 ]?.name || '';
	const excerpt    = post.excerpt.rendered.replace( /<[^>]+>/g, '' ).trim().slice( 0, 130 ) + '...';

	return `
		<article class="wm-card">
			${ imageUrl ? `<a href="${ post.link }" class="wm-card__thumbnail" tabindex="-1" aria-hidden="true"><img src="${ imageUrl }" alt="${ imageAlt }" class="wm-card__img" loading="lazy"></a>` : '' }
			<div class="wm-card__body">
				${ category ? `<span class="wm-card__category">${ category }</span>` : '' }
				<h3 class="wm-card__title"><a href="${ post.link }">${ post.title.rendered }</a></h3>
				<p class="wm-card__excerpt">${ excerpt }</p>
				<a href="${ post.link }" class="wm-card__link">Read more</a>
			</div>
		</article>
	`;
}
