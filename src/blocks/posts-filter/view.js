document.addEventListener( 'DOMContentLoaded', () => {
	const filter = document.querySelector( '[data-block="wm-posts-filter"]' );
	if ( ! filter ) return;

	const clearBtn = filter.querySelector( '[data-action="clear"]' );

	// Set ensures each term ID appears only once — no need to check for duplicates.
	const selected = { categories: new Set(), tags: new Set() };

	// Event delegation — one listener on the container instead of one per pill.
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

	// Toggle a pill: add or remove its term ID from the active selection.
	function handlePillClick( pill ) {
		const group    = pill.closest( '[data-filter-type]' );
		const type     = group?.dataset.filterType; // 'categories' or 'tags'
		const termId   = parseInt( pill.dataset.termId );
		const isActive = pill.getAttribute( 'aria-pressed' ) === 'true'; // aria-pressed doubles as state.

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

	// Reset all active selections and notify the grid.
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

	// Show the clear button only when at least one filter is active.
	function updateClearButton() {
		const hasSelection = selected.categories.size > 0 || selected.tags.size > 0;
		clearBtn.hidden    = ! hasSelection;
	}

	// Dispatch CustomEvent to document — the grid's view.js listens for this.
	// Set is spread into Array because Set cannot be serialized directly.
	// page resets to 1 on every filter change.
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
