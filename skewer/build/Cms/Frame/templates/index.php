<?php
/**
 * @var int
 * @var string $layoutMode
 * @var string $moduleDir
 * @var string $dictVals
 * @var string $ver
 * @var string $lang
 * @var \yii\web\View $this
 */
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\build\Cms;
use skewer\components\design\Design;

$bundle = skewer\build\Cms\Frame\Asset::register($this);

if (($layoutMode == 'editorMap') && (Yii::$app->register->moduleExists(Cms\EditorMap\Module::getNameModule(), Layer::CMS))) {
    Cms\EditorMap\Asset::register(Yii::$app->view);
}

$sTitlePage = Site::getSiteAdmTitleByLayoutMode($layoutMode);

$this->beginPage();
?><!DOCTYPE html>
<html lang="<?= $lang; ?>">
<head>
    <meta charset="utf-8">
    <title><?= $sTitlePage; ?></title>
    <link rel="shortcut icon" href="<?= Design::get('page', 'favicon', 'skewer\build\Page\Main\Asset'); ?>" type="image/png" />

    <script type="text/javascript">
        var sessionId = '<?= $sessionId; ?>';
        var titlePage = '<?= $sTitlePage; ?>';
        var layoutMode = '<?= $layoutMode; ?>';
        var lang = '<?= $lang; ?>';
        var dict = <?= $dictVals; ?>;

        var rootPath = '<?= $bundle->baseUrl . '/js'; ?>';
        var extJsDir = '<?= $this->getAssetManager()->getBundle(skewer\libs\ext_js\Asset::className())->baseUrl; ?>';
        var pmDir = '<?= $this->getAssetManager()->getBundle(skewer\components\ext\Asset::className())->baseUrl; ?>';
        var ckedir = '<?= $this->getAssetManager()->getBundle(\skewer\libs\CKEditor\Asset::className())->baseUrl; ?>';

    </script>

    <?php $this->head(); ?>

</head>
<body>

<?php $this->beginBody(); ?>
    <div id="js_admin_preloader" class="admin-preloader">
        <img src="<?= $bundle->baseUrl; ?>/img/preloader.gif" />

    </div>

    <form id="history-form" class="x-hide-display">
        <input type="hidden" id="x-history-field" />
        <iframe id="x-history-frame"></iframe>
    </form>

<?php $this->endBody(); ?>

</body>
</html>

<?php $this->endPage(); ?>
