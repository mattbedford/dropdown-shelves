<?php
/**
 * Plugin Name: Drilldown Shelves Nav
 * Description: Amazon-style drill-down “shelves” navigation in a Bootstrap Offcanvas or fixed sidebar.
 * Version:     0.2.0
 * Author:      Matt Bedford
 * Author Uri: https://mattbedford.com
 * Plugin Uri: https://github.com/mattbedford/dropdown-shelves.git
 */

namespace CSK\Drilldown;

if (!defined('ABSPATH')) exit;

define(__NAMESPACE__ . '\DIR', plugin_dir_path(__FILE__));
define(__NAMESPACE__ . '\URL', plugin_dir_url(__FILE__));
define(__NAMESPACE__ . '\VER', '0.2.0');
define(__NAMESPACE__ . '\KILLHOVER_DEFAULT', '.primary-navigation > ul.menu');
define(__NAMESPACE__ . '\SOURCE_SELECTOR_DEFAULT', 'ul.menu, ul.nav-menu, ul');

// PSR-4-ish autoloader for \CSK\Drilldown\*
spl_autoload_register(function($class) {
    $ns = __NAMESPACE__ . '\\';
    if (strpos($class, $ns) !== 0) return;
    $rel = substr($class, strlen($ns));
    $path = DIR . 'src/' . str_replace('\\', '/', $rel) . '.php';
    if (is_readable($path)) require $path;
});

Plugin::boot();
