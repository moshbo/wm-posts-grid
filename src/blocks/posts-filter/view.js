import { store, getContext, getElement } from "@wordpress/interactivity";
import "../posts-grid/store";

const { state, actions } = store("wm-posts-grid", {
	actions: {
		// On Categoreis/Tags select, this is filling the state with the selected ID's
		toggleFilter() {
			const context = getContext();
			const type = context.type;
			const termId = context.termId;
			const isActive =
				context.type === "categories"
					? state.selectedCategories.includes(termId)
					: state.selectedTags.includes(termId);

			if (type === "categories") {
				state.selectedCategories = isActive
					? state.selectedCategories.filter((id) => id !== termId)
					: [...state.selectedCategories, termId];
			} else {
				state.selectedTags = isActive
					? state.selectedTags.filter((id) => id !== termId)
					: [...state.selectedTags, termId];
			}

			state.currentPage = 1;
		},

		clearFilters() {
			state.selectedCategories = [];
			state.selectedTags = [];
			state.currentPage = 1;
		},
	},
});
