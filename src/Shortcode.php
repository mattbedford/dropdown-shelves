<?php
namespace CSK\Drilldown;

final class Shortcode {
    public static function renderShortcode(array $atts = []): string {
        $atts = shortcode_atts([
            'menu'          => '',
            'location'      => 'primary',
            'title'         => 'Browse',
            'offcanvas'     => 'start',
            'width'         => '360',
            'id'            => '',
            'button'        => 'Menu',
            'killhover'     => \apply_filters(__NAMESPACE__.'\killhover_selector', KILLHOVER_DEFAULT),
            'source_selector'=> \apply_filters(__NAMESPACE__.'\source_selector', SOURCE_SELECTOR_DEFAULT),
        ], $atts, 'drilldown_nav');

        $menuArgs = [
            'container'   => false,
            'echo'        => false,
            'fallback_cb' => '__return_empty_string',
            'menu_class'  => 'menu nav-menu',
            'menu_id'     => '',
            'depth'       => 0,
        ];

        if (!empty($atts['menu'])) {
            $menuArgs['menu'] = $atts['menu'];
        } else {
            $menuArgs['theme_location'] = $atts['location'];
        }

        $nestedUL = wp_nav_menu($menuArgs);
        if (!$nestedUL) return '';

        $data = [
            'uid'          => $atts['id'] ?: ('ddnav-' . wp_generate_uuid4()),
            'title'        => $atts['title'],
            'width'        => (int) $atts['width'],
            'offcanvas'    => strtolower(trim($atts['offcanvas'])),
            'button'       => $atts['button'],
            'template_ul'  => $nestedUL,
            'noscript_ul'  => $nestedUL,
            'killhover'    => $atts['killhover'],
            'source_selector' => $atts['source_selector'],
        ];

        return self::view('shelf', $data);
    }

    private static function view(string $name, array $data): string {
        $file = DIR . 'views/' . $name . '.php';
        if (!is_readable($file)) return '';
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
