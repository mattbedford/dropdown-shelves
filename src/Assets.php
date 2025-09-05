<?php
namespace CSK\Drilldown;

final class Assets {
    public static function enqueue(): void {
        // CSS
        wp_enqueue_style(
            'csk-ddnav',
            URL . 'assets/css/drilldown-shelves.css',
            [],
            VER
        );

        // JS (vanilla, no deps)
        wp_enqueue_script(
            'csk-ddnav',
            URL . 'assets/js/drilldown-shelves.js',
            [],
            VER,
            true
        );
    }
}