<?php
namespace CSK\Drilldown;

final class Plugin {
    public static function boot(): void {
        add_action('wp_enqueue_scripts', [Assets::class, 'enqueue']);
        add_shortcode('drilldown_nav', [Shortcode::class, 'renderShortcode']);
        // Add a 'no-js' class early for nicer fallback control (removed by our JS)
        add_action('wp_head', function(){
            echo "<script>document.documentElement.classList.add('no-js');</script>\n";
        }, 0);
    }
}