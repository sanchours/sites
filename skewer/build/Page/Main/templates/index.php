<?php

use skewer\build\Design\Zones;
use skewer\components\design\Design;
use skewer\components\preloadResources\PreloadResources;
use skewer\components\seo;
use skewer\helpers\Adaptive;
/*
 * @var $this \yii\web\View
 * @var string $SEOTitle
 * @var string $SEOKeywords
 * @var string $SEODescription
 * @var string $canonical_url
 * @var string $openGraph
 * @var array $page_class
 * @var int $sectionId
 * @var array $_params_
 * @var string $canonical_pagination
 * @var string $adaptive_parameters
 * @var string $favicon
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$pageBundle = \skewer\build\Page\Main\Asset::register($this);

if (Design::modeIsActive()) {
    \skewer\build\Design\Frame\AssetDesign::register($this);
}

// если есть параметр не индекировать
if ((isset($SEONonIndex) && $SEONonIndex)) {
    /** @var string параметр для индексации страницы */
    $sRobots = 'none';
} else {
    $sRobots = 'index,follow';
}

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= \Yii::$app->language; ?>" class="g-no-js" data-cms="canape-cms">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="<?= $sRobots; ?>">

    <?php if (Adaptive::modeIsActive()):?><meta name="viewport" content="width=device-width, initial-scale=1.0"><?php endif; ?>

    <title><?= seo\Api::prepareRawString($SEOTitle); ?></title>
    <?php if ($openGraph):?> <?= $openGraph; ?> <?php endif; ?>
    <meta name="description" content="<?= seo\Api::prepareRawString($SEODescription); ?>">
    <meta name="keywords" content="<?= seo\Api::prepareRawString($SEOKeywords); ?>">
    <?php if ($favicon): ?><?= $favicon; ?><?php endif; ?>
    <?php if (isset($SEOAddMeta) && $SEOAddMeta): ?><?=Html::decode($SEOAddMeta); ?><?php endif; ?>

    <?php if ($canonical_url): ?>
        <link rel="canonical" href="<?= $canonical_url; ?>">
    <?php endif; ?>
    <?php if (!empty($canonical_pagination)): ?>
        <link rel="canonical" href="<?=$canonical_pagination; ?>">
    <?php endif; ?>

    <?= PreloadResources::getTagsLink($sectionId); ?>

    <?php if (isset($aLangLinks)): ?>
        <?php foreach ($aLangLinks as $links): ?>
            <meta rel="alternate" hreflang="<?=Html::decode($links['hreflang']); ?>" href="<?=Html::decode($links['href']); ?>" />
        <?php endforeach; ?>
    <?php endif; ?>

    <?php $this->head(); ?>

    <?php # дополнительный блок в заголовке?>
    <?php if (isset($addHead['text']) && $addHead['text']): ?><?=Html::decode($addHead['text']); ?><?php endif; ?>

    <?php # должен быть непосредственно перед закрывающим тегом </head>?>
    <?php if (isset($gaCode['text']) && $gaCode['text']): ?><?=Html::decode($gaCode['text']); ?><?php endif; ?>

    <script>(function(e,t,n){var r=e.querySelectorAll("html")[0];r.className=r.className.replace(/(^|\s)g-no-js(\s|$)/,"$1g-js$2")})(document,window,0);</script>
</head>

    <?php
    /** @var string $sBodyAttr доп атрибуты для тэга body */
    $sBodyAttr = '';

    if (Design::modeIsActive()) {
        $sBodyAttr .= ' sktag="page"';
        $sBodyAttr .= sprintf(' sectionid="%d"', $sectionId);
    }

    if (isset($specMenu_bodyFontSize)) {
        $sBodyAttr .= ' style="font-size: ' . $specMenu_bodyFontSize . 'px;"';
    }

    if (isset($sBodyClass) && !empty($sBodyClass)) {
        $sBodyAttr .= ' class="' . $sBodyClass . '"';
    }
    ?>

    <body<?= $sBodyAttr; ?>>
        <?php $this->beginBody(); ?>
        <input type="hidden" id="current_language" value="<?= \Yii::$app->language; ?>">
        <input type="hidden" id="current_section" value="<?= $sectionId; ?>">
        <input type="hidden" id="js-adaptive-min-form-width" value="<?=$iMinWidthForForm; ?>">
        <div class="l-layout">
            <div class="layout__wrap">

                <?php
                $headTpl = ArrayHelper::getValue($_params_, ['.layout', 'head_tpl', 0], 'base');
                echo $this->render('head/' . $headTpl . '/tpl', $_params_);
                ?>

                <?php

                $aList = ArrayHelper::getValue($_params_, [Zones\Api::layoutGroupName, Zones\Api::layoutList], ['content']);

                foreach ($aList as $sLayoutName) {
                    if ($sLayoutName === 'content') {
                        $sPrefix = '';
                    } else {
                        $sPrefix = $sLayoutName . '\\';
                    }

                    $sContent = ArrayHelper::getValue($_params_, ['layout', $sPrefix . 'content'], '');
                    if (!$sContent) {
                        continue;
                    }

                    $contentTpl = ArrayHelper::getValue($_params_, ['.layout', $sPrefix . 'content_tpl', 0], 'base');
                    echo $this->render('content/' . $contentTpl . '/tpl', [
                        'content' => $sContent,
                        'left' => ArrayHelper::getValue($_params_, ['layout', $sPrefix . 'left'], ''),
                        'right' => ArrayHelper::getValue($_params_, ['layout', $sPrefix . 'right'], ''),
                        'layoutName' => $sLayoutName,
                    ]);
                }

                ?>

            </div>
            <div class="layout__bgbox">
                <div class="layout__bgwrap">
                    <div class="layout__bgleft"></div>
                    <div class="layout__bgright"></div>
                </div>
            </div>
            <div class="b-loader js-loader"></div>
        </div>

        <?php
        $footerTpl = ArrayHelper::getValue($_params_, ['.layout', 'footer_tpl', 0], 'base');
        echo $this->render('footer/' . $footerTpl . '/tpl', $_params_);
        ?>

        <?= (isset($countersCode['text'])) ? $countersCode['text'] : ''; ?>

        <div id="js-callbackForm" class="js-callbackForm b-callbackform-wrap" style="display: none;"></div>

        <?= $this->render('MicroData'); ?>
        <div class="js_adaptive_params<?php if (!Adaptive::modeIsActive()): ?> g-nodisplay<?php endif; ?>" data-adaptive_parameters='<?=$adaptive_parameters; ?>'></div>

        <?php $this->endBody(); ?>
        <?php if (!empty($addBlockBeforeBodyEnd['text'])): ?><?= Html::decode($addBlockBeforeBodyEnd['text']); ?><?php endif; ?>

    </body>
</html>
<?php $this->endPage(); ?>
