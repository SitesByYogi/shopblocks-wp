# ShopBlocks WP

ShopBlocks provides structured Blogs, landing Articles, shoppable Collections, reusable Block Editor patterns, optional WooCommerce commerce components, and built-in structured data.

## 2.2.5 — Migration Tool Cleanup

- Removed the ShopBlocks Migration admin screen from new and upgraded installations.
- Removed one-time WordPress Post → ShopBlocks Blog conversion actions, batch handlers, collision counters, rollback controls, and migration-only AJAX endpoints.
- Removed the legacy plural `collections` conversion action and its admin controls.
- Retained runtime compatibility for content migrated by earlier ShopBlocks versions.
- Retained Legacy Template Compatibility for previously migrated Collections so existing theme/Elementor rendering is not disrupted.
- Retained ShopBlocks Blog author-archive and feed compatibility.
- Removed obsolete migration instructions and hardcoded `/blogs/` migration guidance from current documentation.
- No database migration is required and existing migrated content is not modified.

## 2.2.4 — Native WordPress Updates

- Added native **Update now** support through WordPress' Plugin Upgrader.
- ShopBlocks now uses the ZIP asset attached to the latest published GitHub Release as the update package.
- Release assets are detected dynamically; filenames must begin with `shopblocks-wp-` and end with `.zip`.
- Assets containing the current release version are preferred.
- If a release has no valid ShopBlocks ZIP asset, the update remains notification-only.
- Added a defensive upgrader source-normalization safeguard to keep the installed plugin directory as `shopblocks-wp`.
- Existing six-hour release caching and manual **Check for updates** functionality remain in place.

## 2.2.3 — Configurable Content Slugs

- Added configurable Blog and Collection URL bases under ShopBlocks Settings.
- Existing installations retain the default `/blogs/` and `/collections/` routes until explicitly changed.
- Added duplicate Blog/Collection slug protection.
- Added warnings when a selected URL base appears to conflict with an existing Page or registered post type.
- Rewrite rules now flush once after a ShopBlocks slug changes instead of on normal requests.
- Added live example URLs in Settings to make routing changes easier to review before deployment.

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
