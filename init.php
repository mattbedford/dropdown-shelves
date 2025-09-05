<?php
/**
 * Plugin Name: Drilldown Shelves Nav
 * Description: Amazon-style drill-down “shelves” navigation in a Bootstrap Offcanvas or fixed sidebar.
 * Version:     2.2.0
 * Author:      Matt Bedford
 * Author Uri: https://mattbedford.com
 * Plugin Uri: https://github.com/mattbedford/dropdown-shelves.git
 */

namespace CSK\Drilldown;

if (!defined('ABSPATH')) { exit; }

const VERSION = '0.3.0';
const SLUG    = 'csk-drilldown-shelves';

/** Paths */
function plugin_url($path = '') { return plugins_url($path, __FILE__); }
function plugin_dir($path = '') { return plugin_dir_path(__FILE__) . ltrim($path, '/'); }

/** Assets */
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets', 20);
function enqueue_assets() {
    wp_register_style( SLUG, plugin_url('assets/css/drilldown-shelves.css'), [], VERSION );
    wp_register_script(SLUG, plugin_url('assets/js/drilldown-shelves.js'), [], VERSION, true);

    wp_enqueue_style(SLUG);
    wp_enqueue_script(SLUG);

    $killhover   = apply_filters(__NAMESPACE__ . '\killhover_selector', '.primary-navigation > ul.menu');
    $sourceSel   = apply_filters(__NAMESPACE__ . '\source_selector',    '.primary-navigation > ul.menu');

    wp_add_inline_script(SLUG, 'window.CSK_DDNAV = ' . wp_json_encode([
            'killhover' => (string) $killhover,
            'sourceSel' => (string) $sourceSel,
        ]), 'before');
}

/** Shortcode: [drilldown_nav menu="menu-games" title="Browse" width="360" button="Menu"] */
add_shortcode('drilldown_nav', __NAMESPACE__ . '\\shortcode');
function shortcode($atts = []) {
    $atts = shortcode_atts([
        'menu'    => '',
        'title'   => 'Browse',
        'width'   => 360,
        'button'  => 'Menu',
        'id'      => '',  // optional stable id
    ], $atts, 'drilldown_nav');

    $menu_arg = $atts['menu'];
    if (!$menu_arg) return '';

    $uid = $atts['id'] ?: 'csk-shelf-' . wp_generate_uuid4();
    $title = $atts['title'];
    $width = (int) $atts['width'];
    $button = $atts['button'];

    // Build the UL once (we’ll drill it via JS)
    $ul = wp_nav_menu([
        'menu'            => $menu_arg,
        'container'       => false,
        'echo'            => false,
        'fallback_cb'     => false,
        'depth'           => 0,
        'items_wrap'      => '<ul class="menu nav-menu">%3$s</ul>',
    ]);

    // Noscript fallback (plain UL)
    $noscript = $ul;

    ob_start();
    include plugin_dir('views/shelf.php');
    return trim(ob_get_clean());
}

/** Kill theme hover dropdowns for the chosen selector (filterable) */
add_action('wp_head', __NAMESPACE__ . '\\inject_killhover_css', 99);
function inject_killhover_css(){
    $kill = trim((string) apply_filters(__NAMESPACE__ . '\killhover_selector', '.primary-navigation > ul.menu'));
    if (!$kill) return;
    $kill = esc_html($kill);
    echo "<style id='csk-ddnav-killhover'>\n"
        . "$kill ul.sub-menu{display:none!important}\n"
        . "$kill li:hover>ul.sub-menu,$kill li:focus-within>ul.sub-menu{display:none!important;opacity:0!important;visibility:hidden!important;pointer-events:none!important}\n"
        . "</style>\n";
}

/** Sensible defaults for Storefront */
add_filter(__NAMESPACE__ . '\killhover_selector', function($sel){
    return $sel ?: '.primary-navigation > ul.menu';
});
add_filter(__NAMESPACE__ . '\source_selector', function($sel){
    return $sel ?: '.primary-navigation > ul.menu';
});