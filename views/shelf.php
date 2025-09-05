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
 * @var string $template_ul
 * @var string $noscript_ul
 * @var string $killhover
 * @var string $source_selector
 */

$rootStyle   = '--ddnav-width:' . (int) $width . 'px;';
$isOffcanvas = in_array($offcanvas, ['start','end'], true);

$uid_attr       = esc_attr($uid);
$title_text     = $title !== '' ? $title : 'Browse';
$title_attr     = esc_attr($title_text);
$rootStyle_attr = esc_attr($rootStyle);
$sourceSel_attr = esc_attr((string) $source_selector);
$wrapStart = $isOffcanvas
    ? '<div class="offcanvas offcanvas-' . ($offcanvas === 'end' ? 'end' : 'start') . '" tabindex="-1" id="' . $uid_attr . '" aria-labelledby="' . $uid_attr . '-label" style="' . $rootStyle_attr . '">'
    : '<div id="' . $uid_attr . '-wrap" class="ddnav-wrap" style="width:' . (int) $width . 'px">';

/** Toggle button only for offcanvas mode */
$button_html = '';
if ($isOffcanvas) {
	$button_html = sprintf(
		'<button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#%1$s" aria-controls="%1$s">%2$s</button>',
		$uid_attr,
		esc_html($button)
	);
}

/** Container start */
$wrapStart = $isOffcanvas
	? sprintf(
		'<div class="offcanvas offcanvas-%1$s" tabindex="-1" id="%2$s" aria-labelledby="%2$s-label">',
		($offcanvas === 'end' ? 'end' : 'start'),
		$uid_attr
	)
	: sprintf(
		'<div id="%1$s-wrap" class="ddnav-wrap" style="width:%2$dpx">',
		$uid_attr,
		(int) $width
	);

$wrapEnd = '</div>';

/** Per-theme hover/focus “kill switch” for dropdowns */
$killCss = '';
$kill = trim((string) $killhover);
if ($kill !== '') {
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

/** Offcanvas header/body vs fixed wrapper */
if ( $isOffcanvas ) : ?>
	<div class="offcanvas-header">
		<h5 id="<?php echo $uid_attr; ?>-label" class="offcanvas-title"><?php echo esc_html( $title_text ); ?></h5>
		<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php esc_attr_e('Close'); ?>"></button>
	</div>
	<div class="offcanvas-body p-0">
<?php endif; ?>

		<div
			class="csk-ddnav is-cloaked"
			data-ddnav="1"
			data-title="<?php echo $title_attr; ?>"
			data-source-selector="<?php echo $sourceSel_attr; ?>"
			style="<?php echo $rootStyle_attr; ?>"
		>
			<div
			  class="csk-ddnav is-cloaked"
			  data-ddnav="1"
			  data-title="<?php echo $title_attr; ?>"
			  data-source-selector="<?php echo $sourceSel_attr; ?>"
			  style="<?php echo $rootStyle_attr; ?>"
			>
			  <div class="stack" aria-live="polite" aria-atomic="false">
				<div class="stack-inner" style="transform: translateX(0%); will-change: transform;"></div>
			  </div>

			  <template class="dd-source">
				<?php echo $template_ul; ?>
			  </template>

			  <noscript>
				<div class="dd-fallback">
				  <?php echo $noscript_ul; ?>
				</div>
			  </noscript>
			</div>

<?php if ( $isOffcanvas ) : ?>
	</div><!-- /.offcanvas-body -->
<?php endif;

echo $wrapEnd;
echo $killCss;
