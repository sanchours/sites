<?php

use skewer\components\design\Design;

/**
 * @var int
 * @var string $layoutMode
 * @var string $moduleDir
 * @var string $dictVals
 * @var string $ver
 * @var string $lang
 * @var \yii\web\View $this
 */
$bundle = skewer\build\Design\Frame\AssetEditor::register($this);

$this->beginPage();

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Дизайнерский режим (панель управления)</title>
    <link rel="shortcut icon" href="<?= Design::get('page', 'favicon', 'skewer\build\Page\Main\Asset'); ?>" type="image/png" />

    <style type="text/css">
        .CodeMirror {
            font-family: monospace;
            #height: 900px !important;
        }
    </style>

    <script type="text/javascript">
        var sessionId = '<?= $sessionId; ?>';
        var lang = '<?= $lang; ?>';
        var dict = <?= $dictVals; ?>;

        var rootPath = '<?= $bundle->baseUrl . '/js'; ?>';
        var rootCmsPath = '<?= $this->getAssetManager()->getBundle(\skewer\build\Cms\Frame\Asset::className())->baseUrl; ?>';
        var extJsDir = '<?= $this->getAssetManager()->getBundle(skewer\libs\ext_js\Asset::className())->baseUrl; ?>';
        var pmDir = '<?= $this->getAssetManager()->getBundle(skewer\components\ext\Asset::className())->baseUrl; ?>';
    </script>
    <?php $this->head(); ?>
</head>
<body>

<?php $this->beginBody(); ?>

<div id="js_admin_preloader" class="admin-preloader">
    <img src="<?= $this->getAssetManager()->getBundle(\skewer\build\Cms\Frame\Asset::className())->baseUrl; ?>/img/preloader.gif" />
</div>

<form id="history-form" class="x-hide-display">
    <input type="hidden" id="x-history-field" />
    <iframe id="x-history-frame"></iframe>
</form>

<?php $this->endBody(); ?>
</body>
</html>

<?php $this->endPage(); ?>