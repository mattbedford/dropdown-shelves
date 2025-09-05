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

// Add shortcode programmatically. Because we can.
add_action('storefront_header', function () {
    echo \CSK\Drilldown\Shortcode::renderShortcode([
        'location'        => 'primary',
        'title'           => 'Browse',
        'offcanvas'       => 'start',
        'width'           => 360,
        'killhover'       => '.primary-navigation > ul.menu',
        'source_selector' => '.primary-navigation > ul.menu',
    ]);
}, 45);


// Willoverride function call OR shortcode vars if required.
add_filter('CSK\Drilldown\killhover_selector', fn() => '#site-navigation .menu > ul');
add_filter('CSK\Drilldown\source_selector',    fn() => '#site-navigation .menu > ul');

// Enable in case of no bootstrap 5
function bootstrap_five_fallback() {
    wp_register_script('csk-ddnav-fallback', false, [], null, true);
    wp_add_inline_script('csk-ddnav-fallback', <<<JS
	(function(){
	  var btns = document.querySelectorAll('[data-bs-toggle="offcanvas"]');
	  if (window.bootstrap && window.bootstrap.Offcanvas) return; // Bootstrap 5 present; do nothing.
	  btns.forEach(function(btn){
		var sel = btn.getAttribute('data-bs-target') || btn.getAttribute('href');
		var panel = sel ? document.querySelector(sel) : null;
		if (!panel) return;
		btn.addEventListener('click', function(e){
		  e.preventDefault();
		  panel.classList.toggle('show');
		  panel.style.visibility = panel.classList.contains('show') ? 'visible' : 'hidden';
		  document.documentElement.classList.toggle('csk-offcanvas-open', panel.classList.contains('show'));
		});
	  });
	})();
	JS);
    wp_enqueue_script('csk-ddnav-fallback');
}
add_action('wp_enqueue_scripts', 'CSK\Drilldown\bootstrap_five_fallback', 25);

