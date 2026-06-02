document.addEventListener( 'DOMContentLoaded', () => {
	const filter = document.querySelector( '[data-block="wm-posts-filter"]' );
	if ( ! filter ) return;

	const clearBtn = filter.querySelector( '[data-action="clear"]' );

	// Track active selections per filter type.
	const selected = { categories: new Set(), tags: new Set() };

	filter.addEventListener( 'click', ( e ) => {
		const pill = e.target.closest( '.wm-posts-filter__pill' );
		if ( pill ) {
			handlePillClick( pill );
			return;
		}

		if ( e.target.closest( '[data-action="clear"]' ) ) {
			clearAll();
		}
	} );

	function handlePillClick( pill ) {
		const group    = pill.closest( '[data-filter-type]' );
		const type     = group?.dataset.filterType; // 'categories' or 'tags'
		const termId   = parseInt( pill.dataset.termId );
		const isActive = pill.getAttribute( 'aria-pressed' ) === 'true';

		if ( isActive ) {
			selected[ type ].delete( termId );
			pill.setAttribute( 'aria-pressed', 'false' );
			pill.classList.remove( 'is-active' );
		} else {
			selected[ type ].add( termId );
			pill.setAttribute( 'aria-pressed', 'true' );
			pill.classList.add( 'is-active' );
		}

		updateClearButton();
		dispatchFilterChange();
	}

	function clearAll() {
		selected.categories.clear();
		selected.tags.clear();

		filter.querySelectorAll( '.wm-posts-filter__pill.is-active' ).forEach( ( pill ) => {
			pill.classList.remove( 'is-active' );
			pill.setAttribute( 'aria-pressed', 'false' );
		} );

		updateClearButton();
		dispatchFilterChange();
	}

	function updateClearButton() {
		const hasSelection = selected.categories.size > 0 || selected.tags.size > 0;
		clearBtn.hidden    = ! hasSelection;
	}

	function dispatchFilterChange() {
		document.dispatchEvent( new CustomEvent( 'wm:filter-change', {
			detail: {
				categories: [ ...selected.categories ],
				tags:        [ ...selected.tags ],
				page:        1,
			},
		} ) );
	}
} );
