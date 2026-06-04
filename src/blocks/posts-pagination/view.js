import { store, getContext } from "@wordpress/interactivity";
import "../posts-grid/store";

const { state, actions } = store("wm-posts-grid", {
	actions: {
		goToPage() {
			const context = getContext();
			state.currentPage = context.page;
		},
	},
	callbacks: {
		renderPagination() {
			const { totalPages, currentPage } = state;
			const pagination = document.querySelector(
				'[data-block="wm-posts-pagination"]',
			);
			if (!pagination) return;

			const pagesEl = pagination.querySelector(".wm-posts-pagination__pages");

			if (totalPages <= 1) {
				pagesEl.innerHTML = "";
				pagination.hidden = true;
				return;
			}

			pagination.hidden = false;
			const pages = buildPageList(totalPages, currentPage);

			pagesEl.innerHTML = pages
				.map((p) => {
					if (p === "...") {
						return '<span class="wm-posts-pagination__ellipsis">…</span>';
					}
					const active = p === currentPage ? "is-active" : "";
					return `<button class="wm-posts-pagination__btn ${active}" data-page="${p}" ${
						p === currentPage ? 'aria-current="page"' : ""
					}>${p}</button>`;
				})
				.join("");

			pagesEl.addEventListener("click", (e) => {
				const btn = e.target.closest("[data-page]");
				if (!btn) return;
				state.currentPage = parseInt(btn.dataset.page);
			});
		},
	},
});

function buildPageList(total, current) {
	const pages = new Set([1, total, current - 1, current, current + 1]);
	const valid = [...pages]
		.filter((p) => p >= 1 && p <= total)
		.sort((a, b) => a - b);

	const result = [];
	for (let i = 0; i < valid.length; i++) {
		if (i > 0 && valid[i] - valid[i - 1] > 1) {
			result.push("...");
		}
		result.push(valid[i]);
	}
	return result;
}
