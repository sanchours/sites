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
$bundle = skewer\build\Design\Frame\AssetIndex::register($this);

$this->beginPage();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" >
<head>
    <meta charset="utf-8"  content="text/html"/>
    <title><?= \Yii::t('adm', 'design_page_title'); ?></title>
    <link rel="shortcut icon" href="<?= Design::get('page', 'favicon', 'skewer\build\Page\Main\Asset'); ?>" type="image/png" />
    <?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
<iframe id="skDesignDisplayFrame" class="b-framebox" src="/design/?mode=loading"></iframe>
<div id="skDesignFrameOpener" class="b-framebox_up">
     <iframe id="skDesignEditorFrame" src="/design/?mode=editor"></iframe>
    <div class="frame__link"></div>
</div>
<?php $this->endBody(); ?>
</body>
</html>

<?php $this->endPage(); ?>