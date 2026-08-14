# ShopBlocks WP

ShopBlocks provides structured Blogs, landing Articles, shoppable Collections, reusable Block Editor patterns, optional WooCommerce commerce components, and built-in structured data. Native WordPress Posts remain untouched unless an administrator explicitly runs the Posts → ShopBlocks Blogs migration tool.

## 2.2.2 — Notification-Only GitHub Updates

- Added native WordPress update notifications based on the latest published GitHub Release.
- ShopBlocks now surfaces a normal Plugins-dashboard update notice when a newer release exists.
- Update checks are notification-only; no package URL is supplied and WordPress will not install the release automatically.
- Added a six-hour GitHub release cache to reduce unnecessary API requests.
- Added a manual **Check for updates** action on the Plugins screen for administrators.
- Draft and prerelease GitHub releases are ignored.

## 2.2.1 — Global Blog Newsletter / Signup Embeds

- Added a global Blog newsletter/signup embed setting supporting WordPress shortcodes and safe HTML.
- Added per-Blog newsletter/signup overrides with global fallback behavior.
- Preserved the existing newsletter visibility toggle for per-Blog opt-out.
- Newsletter/signup modules render before sidebar products.
- Kept the original shortcode-only option as a backward-compatible fallback for upgrades from 2.2.0 and earlier.

## 2.2.0 — Integrated Schema + Collection Migration

- Added global Default Blog Sidebar Product IDs with per-Blog override/disable behavior.
- Added native WordPress Post → ShopBlocks Blog batch migration with rollback markers.
- Centered standard Blog hero title/meta structurally while preserving split conversion heroes.
- Added global Default Blog Sidebar Product IDs with per-Blog override and per-Blog disable behavior.

- Fixed Design/General settings isolation so saving CTA colors cannot wipe other ShopBlocks design tokens.
- Added resilient default styling for Collection/shoppable product cards, CTAs, Blog layouts, sidebars, newsletter blocks, and FAQs.
- Empty or invalid saved design tokens now fall back safely instead of producing broken CSS variables.

- Merged the Schema Rich Snippets CollectionPage/Product JSON-LD engine into ShopBlocks.
- Added native schema support for modern ShopBlocks Collections, shortcode-driven shoppable content, and WooCommerce product-category archives.
- Added duplicate-schema protection while the legacy Schema Rich Snippets plugin remains active during a staged migration.
- Related-content panels and their buttons now inherit the saved ShopBlocks Primary Color by default instead of using an independent hardcoded blue.
- Removed the obsolete prototype `shoppable_article` CPT and its Elementor/title compatibility hooks; modern Blogs, Articles, and Collections are the supported content types.
- `[shoppable_product_top]` and `[add_products]` continue to resolve through the modern ShopBlocks renderers.
- Added **ShopBlocks → Legacy Migration** to safely convert old plural `collections` records to the modern `collection` post type in-place while preserving IDs, slugs, content, metadata, thumbnails, and dates.
- Added a Structured Data setting so schema output can be disabled when another SEO/schema system owns CollectionPage markup.
- Added a batched **WordPress Posts → ShopBlocks Blogs** migration with progress reporting, slug-collision protection, migration markers, and rollback support.
- Post migration preserves the original database ID, slug, Block Editor/Elementor content, featured image, author, dates, comments, revisions, categories/tags, SEO metadata, and other custom fields.
- Migrated native Posts default to the standard ShopBlocks Blog layout so their complete existing post body renders inside the ShopBlocks article area.
- Added author-archive and feed compatibility for migrated ShopBlocks Blogs.


## 2.1.0 — Universal Article Framework

- WooCommerce is now optional; Blogs and Articles work on lead-generation and editorial WordPress sites without commerce.
- Added Blog Post and Article / Landing Article presentation modes to the existing ShopBlocks Blog content type.
- Added lead-generation hero fields with eyebrow, description, primary/secondary CTAs, and provider-agnostic form or booking shortcode support.
- Added reusable Blog sidebar CTA cards and sidebar form modules.
- Added optional Article location section with map embed URL, address, phone, and email.
- Added dedicated `single-article.php` template and scoped Article Template CSS setting.
- Existing Blogs default to the 2.0 Blog template, preserving current sidebar and WooCommerce behavior.
- Existing Collections and commerce shortcodes remain backward compatible.
- Newsletter markup is omitted completely when no newsletter shortcode is configured.
- Added Lead Generation Article block pattern.


## Migrating from Schema Rich Snippets

For sites using the older **Schema Rich Snippets** plugin:

1. Install and activate ShopBlocks 2.2 while Schema Rich Snippets is still active.
2. Open **ShopBlocks → Legacy Migration**.
3. If legacy plural `collections` records are detected, run **Migrate Legacy Collections**. The migration preserves post IDs, slugs, content, metadata, featured images, and dates.
4. Migrated Collections stay on their original theme/Elementor single template by default through **Legacy Template Compatibility**, preventing an immediate visual template switch.
5. Verify legacy `[shoppable_product_top]` and `[add_products]` content on staging.
6. Deactivate Schema Rich Snippets. ShopBlocks then takes over CollectionPage/Product JSON-LD automatically.

ShopBlocks suppresses its own schema output while Schema Rich Snippets is active to avoid duplicate JSON-LD during the migration window.


## Migrating native WordPress Posts into ShopBlocks Blogs

For a site whose existing editorial posts already used `/blogs/%postname%/` and should now be owned by ShopBlocks:

1. Back up the database or create a staging snapshot.
2. Set **Settings → Permalinks** to `/%postname%/`. ShopBlocks itself owns the `/blogs/` rewrite base.
3. Open **ShopBlocks → Migration** and review the counts and slug-collision warning.
4. Start with **Migrate Published Posts** on staging or a small production test window. Migration runs in batches of 25 to avoid PHP timeouts.
5. Verify several `/blogs/slug/` URLs, featured images, authors/dates, categories/tags, SEO metadata, article content, sidebar rendering, search/archive behavior, and XML sitemaps.
6. Run **Migrate All Editorial Posts** after verification if drafts, scheduled, pending, and private posts should also move.
7. A rollback control is available only for records marked as migrated by ShopBlocks. Rollback URLs follow the site's current native Post permalink structure.

Slug collisions with an existing ShopBlocks Blog are skipped instead of overwritten.

## Content types

### Blogs

Editorial articles available at `/blogs/`.

- Featured-image hero with title, date, and author
- Main article content created in the Block Editor
- Right sidebar on desktop and stacked sidebar on mobile
- Optional newsletter panel
- Optional WooCommerce sidebar products
- Helpful links
- No automatic product grid at the top

### Collections

Full-width shoppable articles available at `/collections/`.

- Centered title and excerpt
- Selected WooCommerce products rendered near the top
- Four-column desktop and two-column mobile product grid
- Optional related Blog cards
- Long-form Block Editor content
- No sidebar

## Block patterns

Open the Block Editor pattern inserter and select **ShopBlocks Layouts**.

- Standard Blog Article
- Blog Sidebar information panel
- Shoppable Collection Article
- Product hero, CTA, FAQ, and supporting patterns

## Shortcodes

```text
[shopblocks_products ids="1,2,3" columns="4"]
[shopblocks_products ids="1,2" columns="1" layout="sidebar"]
[shoppable_product_top id="123"]
[shopblocks_newsletter]
```

The legacy `[add_products]` shortcode remains supported.

## Template overrides

Copy templates into the active theme:

```text
shopblocks/single-blog.php
shopblocks/archive-blog.php
shopblocks/single-collection.php
shopblocks/archive-collection.php
```

## Requirements

- WordPress 6.3+
- PHP 7.4+
- WooCommerce 7.0+


## 1.7.1

- Center Collection product rows automatically when fewer than four products are displayed.
- Preserve the two-column Collection product layout on mobile.


## 1.7.1
- Rebuilt the dedicated Blog front-end styling to match the supplied editorial design.
- Added responsive featured-image hero treatment and centered title metadata.
- Refined desktop article/sidebar proportions and mobile stacking.
- Restyled newsletter, sidebar products, helpful links, and related Blog cards.
- Scoped all new styling to ShopBlocks Blogs so Collections remain unchanged.


## 1.7.1
- Restored a clearly visible ShopBlocks → Settings submenu.
- Preserved the Custom CSS, newsletter shortcode, product limit, and plugin styling controls.

## Version 1.8.4

- Added stable semantic Blog and Collection template classes while retaining legacy classes.
- Added a Design settings tab with reusable typography, color, width, spacing, radius, and shadow tokens.
- Added generated CSS custom properties for rapid Figma-to-site styling.
- Preserved site-specific Custom CSS as the final override layer.
- Continued support for theme template overrides under `/shopblocks/`.


## 1.8.4
- Changed the default stylesheet to structure-only styling so active theme typography, colors, links, buttons, and forms are inherited.
- Added separately scoped Blog and Collection CSS fields.
- Shared template CSS is automatically isolated to ShopBlocks templates.


## 1.8.4
- Single Blogs and Collections now render inside the active theme's normal singular template so theme and builder headers/footers are preserved.
- Collection article content now uses the full configured ShopBlocks page width instead of the narrower Blog article width.

## 1.9.0

- Collections now render related content automatically outside the Block Editor article body.
- Related content can mix ShopBlocks Blogs and standard WordPress Posts.
- Related cards include featured image, title, excerpt, permalink, and configurable button label.
- Added structured Collection FAQs rendered automatically after native Block Editor content.
- Added stable `.shopblocks-page`, `.shopblocks-page__inner`, and `.shopblocks-gutenberg-content` wrappers.
- Added structural default styling for the Figma-style related-content panel while preserving theme typography.
- Existing numeric related Blog IDs remain backward compatible.

## 1.10.1 Blog template update

- Dedicated Blog hero and two-column editorial shell.
- Dedicated ShopBlocks sidebar; the active theme's normal post sidebar is never loaded.
- Sidebar order: Newsletter, featured products, Helpful Links, optional custom content.
- Removed Related Blogs/Posts cards from Blogs. Related-content cards remain Collection-only.
- Added structured Blog FAQs rendered after native Block Editor content.
- Mobile order: hero, article, FAQs, sidebar, footer.


## 1.10.1
- Fixed empty saved design-token values overriding CSS fallbacks.
- Restored the desktop Blog article/sidebar grid for upgraded installations.
- Kept the mobile stacked sidebar behavior unchanged.
