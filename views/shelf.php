<?php
/**
 * Shelf view.
 *
 * Expected vars:
 * @var string $uid
 * @var string $title
 * @var int    $width
 * @var string $offcanvas        start|end|fixed
 * @var string $button
 * @var string $template_ul      Nested UL from wp_nav_menu
 * @var string $noscript_ul      Same UL for <noscript> fallback
 * @var string $killhover        CSS selector for the theme’s main menu container
 * @var string $source_selector  Selector to find the source UL inside <template>
 */

$rootStyle   = '--ddnav-width:' . (int) $width . 'px;';
$isOffcanvas = in_array($offcanvas, ['start','end'], true, true);

$uid_attr        = esc_attr($uid);
$title_attr      = esc_attr($title);
$rootStyle_attr  = esc_attr($rootStyle);
$sourceSel_attr  = esc_attr((string) $source_selector);

$button_html = '';
if ($isOffcanvas) {
    $button_html = '<button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#' . $uid_attr . '" aria-controls="' . $uid_attr . '">' . esc_html($button) . '</button>';
}

$wrapStart = $isOffcanvas
    ? '<div class="offcanvas offcanvas-' . ($offcanvas === 'end' ? 'end' : 'start') . '" tabindex="-1" id="' . $uid_attr . '" aria-labelledby="' . $uid_attr . '-label">'
    : '<div id="' . $uid_attr . '-wrap" class="ddnav-wrap" style="width:' . (int) $width . 'px">';

$wrapEnd = '</div>';

/** Per-theme hover/focus “kill switch” for dropdowns */
$killCss = '';
$kill = trim((string) $killhover);
if ($kill !== '') {
    // Keep simple: escape for safe insertion into a <style> block.
    $killSafe = esc_html($kill);
    $killCss = <<<CSS
<style>
/* Drilldown: disable theme hover/focus dropdowns for selected nav */
{$killSafe} ul.sub-menu { display: none !important; }
{$killSafe} li:hover > ul.sub-menu,
{$killSafe} li:focus-within > ul.sub-menu {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  pointer-events: none !important;
}
</style>
CSS;
}

echo $button_html;
echo $wrapStart;
?>
    <div class="csk-ddnav is-cloaked"
         data-ddnav="1"
         data-title="<?php echo $title_attr; ?>"
         data-source-selector="<?php echo $sourceSel_attr; ?>"
         style="<?php echo $rootStyle_attr; ?>">
        <div class="stack" aria-live="polite" aria-atomic="false"></div>

        <template class="dd-source">
            <?php echo $template_ul; ?>
        </template>

        <noscript>
            <div class="dd-fallback">
                <?php echo $noscript_ul; ?>
            </div>
        </noscript>
    </div>
<?php
echo $wrapEnd;
echo $killCss;
