document.addEventListener( 'DOMContentLoaded', () => {
	const pagination = document.querySelector( '[data-block="wm-posts-pagination"]' );
	if ( ! pagination ) return;

	const pagesEl = pagination.querySelector( '.wm-posts-pagination__pages' );

	// Bootstrap from initial server-rendered grid state.
	const grid        = document.querySelector( '[data-block="wm-posts-grid"]' );
	const initialData = grid?.querySelector( '.wm-posts-grid__items' );

	if ( initialData ) {
		const total   = parseInt( initialData.dataset.totalPages )  || 1;
		const current = parseInt( initialData.dataset.currentPage ) || 1;
		render( total, current );
	}

	document.addEventListener( 'wm:pagination-update', ( e ) => {
		render( e.detail.totalPages, e.detail.currentPage );
	} );

	pagesEl.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( '[data-page]' );
		if ( ! btn || btn.disabled ) return;

		const page = parseInt( btn.dataset.page );
		document.dispatchEvent( new CustomEvent( 'wm:page-change', { detail: { page } } ) );
	} );

	function render( totalPages, currentPage ) {
		if ( totalPages <= 1 ) {
			pagesEl.innerHTML = '';
			pagination.hidden = true;
			return;
		}

		pagination.hidden = false;
		const pages       = buildPageList( totalPages, currentPage );

		pagesEl.innerHTML = pages
			.map( ( p ) => {
				if ( p === '...' ) {
					return '<span class="wm-posts-pagination__ellipsis">…</span>';
				}
				const active = p === currentPage ? 'is-active' : '';
				return `<button class="wm-posts-pagination__btn ${ active }" data-page="${ p }" ${ p === currentPage ? 'aria-current="page"' : '' }>${ p }</button>`;
			} )
			.join( '' );
	}

	// Show first, last, current ±1, with ellipsis in between.
	function buildPageList( total, current ) {
		const pages = new Set( [ 1, total, current - 1, current, current + 1 ] );
		const valid = [ ...pages ].filter( ( p ) => p >= 1 && p <= total ).sort( ( a, b ) => a - b );

		const result = [];
		for ( let i = 0; i < valid.length; i++ ) {
			if ( i > 0 && valid[ i ] - valid[ i - 1 ] > 1 ) {
				result.push( '...' );
			}
			result.push( valid[ i ] );
		}
		return result;
	}
} );
