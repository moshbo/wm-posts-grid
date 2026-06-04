import { store, getContext, getState } from "@wordpress/interactivity";

let state;
({ state } = store("wm-posts-grid", {
	state: {
		selectedCategories: [],
		selectedTags: [],
		currentPage: 1,
		totalPages: 1,
		isLoading: false,
		posts: [],
		get hasSelection() {
			return (
				state.selectedCategories.length > 0 || state.selectedTags.length > 0
			);
		},
		get isPillActive() {
			const context = getContext();
			return context.type === "categories"
				? state.selectedCategories.includes(context.termId) // Categories
				: state.selectedTags.includes(context.termId); // Tags
		},
	},
}));
