<?php
/** @var string $uid @var string $title @var int $width @var string $button @var string $ul @var string $noscript */
$rootStyle = '--ddnav-width:' . (int)$width . 'px;';
$uid_attr  = esc_attr($uid);
?>
<div class="csk-shelf-wrap">
    <button class="csk-shelf-open btn btn-outline-secondary"
            type="button"
            data-csk-open="#<?php echo $uid_attr; ?>"
            aria-controls="<?php echo $uid_attr; ?>"
            aria-expanded="false">
        <?php echo esc_html($button); ?>
    </button>

    <div id="<?php echo $uid_attr; ?>"
         class="csk-shelf"
         role="dialog"
         aria-modal="true"
         aria-labelledby="<?php echo $uid_attr; ?>-label"
         aria-hidden="true">

        <div class="csk-shelf-backdrop" data-csk-close></div>

        <div class="csk-shelf-panel" style="<?php echo esc_attr($rootStyle); ?>">
            <div class="csk-ddnav is-cloaked"
                 data-ddnav="1"
                 data-title="<?php echo esc_attr($title); ?>">
                <div class="stack"></div>

                <template class="dd-source"><?php echo $ul; ?></template>

                <noscript>
                    <div class="dd-fallback">
                        <h3 id="<?php echo $uid_attr; ?>-label"><?php echo esc_html($title); ?></h3>
                        <?php echo $noscript; ?>
                    </div>
                </noscript>
            </div>

        </div>
    </div>
</div>
