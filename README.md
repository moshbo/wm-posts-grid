# WM Posts Grid

A WordPress plugin that provides two custom Gutenberg blocks for displaying and filtering articles.

## Requirements

- WordPress 6.4+
- PHP 8.1+
- Node.js 20+ (for development)

## Installation

1. Upload the `wm-posts-grid` folder to `wp-content/plugins/`
2. Activate the plugin in **Plugins → Installed Plugins**
3. On activation the plugin automatically:
   - Creates the `wm_article` custom post type and its taxonomies
   - Seeds 11 demo articles with images, categories, and tags
   - Creates a demo page at `/wm-articles/` with both blocks already placed

An admin notice will confirm activation with links to the articles list and the demo page.

## Blocks

### WM Posts Grid (`wm/posts-grid`)

Dynamic block that fetches and displays `wm_article` posts via PHP server-side rendering. Inspector Controls allow the editor to configure:

- **Desktop / Tablet / Mobile columns** — independent column count per breakpoint (rendered as CSS custom properties, applied via media queries)
- **Posts per page** — controls the `WP_Query` page size and REST API `per_page` parameter

The block includes a **WM Posts Pagination** inner block, which is locked and cannot be removed.

### WM Posts Filter (`wm/posts-filter`)

Dynamic block that renders all `wm_category` and `wm_tag` terms as pill-shaped toggle buttons. Supports multiple simultaneous selections within each filter type.

### WM Posts Pagination (`wm/posts-pagination`)

Inner block of WM Posts Grid. Renders a page number navigator that is updated dynamically by JavaScript after each filter or page change.

## Architecture Decisions

### Inter-block Communication: WordPress Interactivity API

The filter, grid, and pagination blocks share state via the WordPress Interactivity API (`@wordpress/interactivity`). A shared store named `wm-posts-grid` holds all reactive state:

- `selectedCategories` — array of selected category term IDs
- `selectedTags` — array of selected tag term IDs
- `currentPage` — current pagination page
- `posts` — array of fetched posts (updated after each filter or page change)
- `hasSelection` — derived boolean, true when any filter is active
- `isPillActive` — derived boolean, true when a specific pill is selected (read via `getContext()`)

**Why Interactivity API?**
The Interactivity API is WordPress's official solution for reactive frontend block behavior. It provides a shared reactive store, declarative HTML directives, and native ES module support via `viewScriptModule` — removing the need for manual event dispatching and listening.

**Key implementation details:**
- `data-wp-interactive="wm-posts-grid"` — binds an element to the store
- `data-wp-context='{"termId": 5, "type": "categories"}'` — passes per-element data accessible via `getContext()`
- `data-wp-watch="callbacks.watchFilters"` — re-runs the callback whenever accessed state changes
- `data-wp-init="callbacks.watchFilters"` — runs the callback once on block initialization
- `wp_interactivity_config()` — passes PHP data (REST API URL, nonce) to view scripts, compatible with ES modules

### Responsive Columns: CSS Custom Properties

Column counts per breakpoint are stored as block attributes (`columnsDesktop`, `columnsTablet`, `columnsMobile`) and output by `render.php` as inline CSS custom properties (`--wm-cols-desktop`, etc.). The stylesheet applies them via media queries. This means the PHP layer does not need to know about breakpoints — CSS owns that logic.

### Server-Side Rendering + Client-Side Filtering

The initial page load renders posts via PHP (`render.php`), which is good for SEO and perceived performance. After any filter or pagination interaction, `view.js` fetches updated posts from the REST API (`/wp-json/wp/v2/wm-articles`) and replaces the grid HTML in place.

The REST API filtering maps directly to the filter logic requirement:

- OR within a type: `wm-categories=1,2` → `tax_query` with `operator: IN`
- AND across types: separate `tax_query` clauses with `relation: AND`

### Query Caching: Transients API

`render.php` caches the full rendered HTML in a WordPress transient keyed by a hash of the block attributes and current page number. Cache TTL is 1 hour. The cache is invalidated on `save_post_wm_article` and `deleted_post` hooks.

**Tradeoff:** When an article is saved or deleted, the cache is cleared and the updated content is only visible to users after a page refresh — not in real time.

### Isolation via Custom Taxonomies

The plugin uses `wm_category` and `wm_tag` taxonomies (not the built-in `category` and `post_tag`) registered exclusively for `wm_article`. This prevents any overlap with other post types and allows complete cleanup on uninstall.

### Demo Content Seeding

Article data (titles, excerpts, content, image seeds, taxonomy assignments) is defined in `includes/demo-content.php`. Images are downloaded from `https://picsum.photos/seed/{seed}/1200/630` on activation and uploaded to the WordPress media library as standard attachments. All created IDs are stored in `wp_options` for reliable cleanup.

## Uninstall

Deleting the plugin from **Plugins → Installed Plugins → Delete** removes:

- All `wm_article` posts
- All uploaded demo images (attachments)
- All `wm_category` and `wm_tag` terms created by the seeder
- The demo page
- All plugin options
- All cached transients

## Development

All source files are in `src/`. After any change to `src/`, you must run `npm run build` for WordPress to pick up the changes — WordPress reads only from `build/`.

```bash
npm install
npm run build      # production build — required after every src/ change
npm run start      # watch mode — auto-builds on every file save
npm run lint:js    # JS lint
npm run lint:css   # SCSS lint
```

### File Structure

```
wm-posts-grid/
├── wm-posts-grid.php          — plugin bootstrap, block registration, hooks
├── uninstall.php              — full data cleanup on plugin deletion
├── includes/
│   ├── class-post-type.php    — CPT and taxonomy registration
│   ├── class-activator.php    — content seeding and demo page creation
│   ├── class-deactivator.php  — flush rewrite rules
│   └── demo-content.php       — article data config (titles, excerpts, image seeds)
├── src/
│   ├── blocks/
│   │   ├── posts-grid/        — grid block (edit.js, render.php, view.js, style.scss)
│   │   ├── posts-filter/      — filter block (edit.js, render.php, view.js, style.scss)
│   │   └── posts-pagination/  — pagination inner block (edit.js, render.php, view.js, style.scss)
│   └── shared/
│       └── _variables.scss    — design tokens (colours, typography, breakpoints)
└── build/                     — compiled output (committed to git, ready to use without a build step)
```

## Tradeoffs & Known Limitations

- **Client-side filtering via REST API** — Every filter or pagination interaction triggers a new REST API request to the database. This approach suits sites with dynamic content. For fully static sites, a better approach would be to load all posts once and filter from the DOM. For sites with thousands of posts, consider splitting content into separate pages per category to avoid deep pagination.

- **Cache covers initial load only** — The Transients cache applies to the server-rendered HTML on first load. Filtered results fetched via REST API are always live from the database and are not cached.

- **Cache invalidation is not real-time** — The cache is cleared when an article is saved or deleted, but visitors will only see the updated content after a page refresh.

- **REST API visibility** — The `wm_article` post type is exposed via the REST API (`show_in_rest: true`), which makes posts publicly readable. If articles need to be private, additional REST API authentication would be required.

- **Image download on activation** — Demo images are downloaded from `picsum.photos` on activation. If the request times out, the post is still created without a featured image.
