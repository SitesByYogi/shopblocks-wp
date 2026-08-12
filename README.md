# ShopBlocks WP

ShopBlocks adds two purpose-built public content types for WooCommerce publishing while leaving native WordPress Posts untouched.

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
- Related ShopBlocks Blog cards
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
