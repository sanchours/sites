<?php
/**
 * @var string
 * @var string $adaptive_menu_content
 */
?>

    <div class="l-sidebar hide-on-desktop show-on-tablet-l js-sidebar js-sidebar-hide"></div>
    <div class="l-sidebar-block l-sidebar-block--left hide-on-desktop show-on-tablet-l js-sidebar-block">
        <div class="b-sidebar">
            <div class="sidebar__close ">
                <div class="sidebar__close-btn js-sidebar-hide"></div>
            </div>
            <div class="sidebar__content">
                <?= $adaptive_menu_content; ?>
            </div>
        </div>
    </div>

    <div class="l-sidebar js-sidebar-catalog js-sidebar-catalog-hide"></div>
    <div class="l-sidebar-block l-sidebar-block--left js-sidebar-catalog-block">
        <div class="b-sidebar-title"><?=Yii::t('catalog', 'module_name'); ?></div>
        <div class="b-sidebar">
            <div class="sidebar__close">
                <div class="sidebar__close-btn js-sidebar-catalog-hide"></div>
            </div>
            <div class="sidebar__content">
                <?= $adaptive_menu_catalog; ?>
            </div>
        </div>
    </div>