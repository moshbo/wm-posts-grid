import {
	store,
	getContext,
	getConfig,
	getElement,
} from "@wordpress/interactivity";
import "./store.js";

const { state, actions } = store("wm-posts-grid", {
	actions: {
		async fetchPosts() {
			const { apiUrl, nonce } = getConfig();
			const context = getContext();
			const postsPerPage = context.postsPerPage || 6;

			const params = new URLSearchParams({
				per_page: postsPerPage,
				page: state.currentPage,
				_embed: "wp:featuredmedia,wp:term",
			});

			if (state.selectedCategories.length) {
				params.set("wm-categories", state.selectedCategories.join(","));
			}
			if (state.selectedTags.length) {
				params.set("wm-tags", state.selectedTags.join(","));
			}

			state.isLoading = true;

			try {
				const response = await fetch(
					`${apiUrl}wp/v2/wm-articles?${params.toString()}`,
					{
						headers: { "X-WP-Nonce": nonce },
					},
				);
				const posts = await response.json();
				const totalPages =
					parseInt(response.headers.get("X-WP-TotalPages")) || 1;

				state.totalPages = totalPages;
				state.posts = posts;
			} catch (err) {
				console.error("WM Posts Grid: failed to fetch posts", err);
			} finally {
				state.isLoading = false;
			}
		},
	},
	callbacks: {
		watchFilters() {
			// runs every time state.selectedCategories, state.selectedTags or state.currentPage changes
			const { selectedCategories, selectedTags, currentPage } = state;
			actions.fetchPosts();
		},
		renderPosts() {
			if (!state.posts) return;
			const { ref } = getElement();
			ref.innerHTML = state.posts.length
				? state.posts.map(renderCard).join("")
				: '<p class="wm-posts-grid__empty">No articles found.</p>';
		},
	},
});

function renderCard(post) {
	const media = post._embedded?.["wp:featuredmedia"]?.[0];
	const imageUrl =
		media?.media_details?.sizes?.medium_large?.source_url ||
		media?.source_url ||
		"";
	const imageAlt = media?.alt_text || post.title.rendered;
	const terms = post._embedded?.["wp:term"] || [];
	const categories = terms[0] || [];
	const category = categories[0]?.name || "";
	const excerpt =
		post.excerpt.rendered
			.replace(/<[^>]+>/g, "")
			.trim()
			.slice(0, 130) + "...";

	return `
        <article class="wm-card">
            ${
							imageUrl
								? `<a href="${post.link}" class="wm-card__thumbnail" tabindex="-1" aria-hidden="true"><img src="${imageUrl}" alt="${imageAlt}" class="wm-card__img" loading="lazy"></a>`
								: ""
						}
            <div class="wm-card__body">
                ${
									category
										? `<span class="wm-card__category">${category}</span>`
										: ""
								}
                <h3 class="wm-card__title"><a href="${post.link}">${
									post.title.rendered
								}</a></h3>
                <p class="wm-card__excerpt">${excerpt}</p>
                <a href="${post.link}" class="wm-card__link">Read more</a>
            </div>
        </article>
    `;
}
