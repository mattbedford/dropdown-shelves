# Dropdown Shelves
### Ideated fore Storefront-themed WP/Woo installs, but may work for others too.

## Features
- No duplicate IDs: The menu HTML is placed inside a <template> (inert), which the JS clones—so you won’t trip on duplicate IDs. 
- For no-JS users, a <noscript> block renders the same nested UL as a plain fallback.
- No FOUC: We cloak the shelf (.is-cloaked) and uncloak after the first panel is mounted.
- Storefront hover nuked: CSS disables the theme’s :hover/:focus-within submenus so nothing flashes under the shelf.
- Bootstrap native: If Bootstrap is already present, Offcanvas Just Works™. 
- Minimal PHP surface: One heredoc view, tiny shortcode class, no walker overrides, no template edits.

---
- If you prefer your own drawer, switch offcanvas="fixed" and style the wrapper as you like.
- // In your child theme:
  `\CSK\Drilldown\Shortcode::renderShortcode(['offcanvas' => 'fixed', 'width' => 360]);`
---

## To use
- install & activate plugin
- Insert the shortcode where you want the shelf button (or the fixed sidebar) to render (off-canvas, left is default).

```php
// off-canvas, left
[drilldown_nav menu="menu-games" title="Browse" offcanvas="start" width="360"]

// off-canvas, right
[drilldown_nav offcanvas="end"]

// Fixed sidebar, no off-canvas wrapper
[drilldown_nav offcanvas="fixed" width="320"]
```

- Apply the filters to the specific theme you're using. The filters are:
```php
add_filter('CSK\Drilldown\killhover_selector', fn() => '.main-navigation > ul.menu');
//Scope for CSS hover overrides: where to turn off the theme’s hover/focus dropdown behavior. 
// This should point at the container the theme targets in its CSS rules like 
"X li:hover > ul.sub-menu { … }" 
// We inject a tiny <style> scoped to that selector so only that nav is affected
```

```php
add_filter('CSK\Drilldown\source_selector',  fn() => 'nav#site-navigation ul, ul.primary-menu');`
//Dom picker for the <ul> element we're targetting.
// where our JS should find the actual nested <ul> to clone into panels. 
// This must match the <ul> that contains the .menu-item / .menu-item-has-children LIs, inside our <template>
```

> [!TIP]
> These two are different because the CSS needs a scope large enough to catch the theme’s :hover rules but not so large that you nuke other menus.
> Our JS needs the UL itself to clone the nested structure. Passing the container would fail if there are wrappers around the UL.


# Cheatsheet for common themes
Storefront
- killhover_selector: .primary-navigation > ul.menu
- source_selector: .primary-navigation > ul.menu

Astra
- killhover_selector: .main-header-menu (that’s the UL)
- source_selector: .main-header-menu

GeneratePress
- killhover_selector: .main-navigation .main-nav > ul
- source_selector: .main-navigation .main-nav > ul

Neve
- killhover_selector: nav#site-navigation .primary-menu (UL often has .primary-menu)
- source_selector: nav#site-navigation .primary-menu

Hello Elementor
- killhover_selector: .elementor-nav-menu (UL)
- source_selector: .elementor-nav-menu

Block themes (Twenty Twenty-Two/Three+)
- killhover_selector: nav.wp-block-navigation
- source_selector: nav.wp-block-navigation ul.wp-block-navigation__container