<?php
/**
 * @var string
 * @var \yii\web\View $this
 */
use skewer\components\design\Design;

$bundle = skewer\build\Cms\Frame\Asset::register($this);

$moduleDir = $bundle->baseUrl;

$this->beginPage();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" >
<head>
    <meta charset="utf-8"  content="text/html"/>
    <title>Canape CMS</title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <link rel="shortcut icon" href="<?= Design::get('page', 'favicon', 'skewer\build\Page\Main\Asset'); ?>" type="image/png" />

    <link rel="stylesheet" type="text/css" href="<?= $moduleDir; ?>/css/main.css" media="all" />
    <link rel="stylesheet" type="text/css" href="<?= $moduleDir; ?>/css/main_br.css" media="all" />
    <!--[if lte IE 7]>
    <link rel="stylesheet" href="<?= $moduleDir; ?>/css/ie/main.ie.css" />
    <![endif]-->

</head>
<body>

<?php $this->beginBody(); ?>

<div class="b-msgbox">
    <h1><?= \Yii::t('adm', 'browser_h1'); ?></h1>
    <p><?= \Yii::t('adm', 'browser_text'); ?></p>
    <p>
        <a href="https://www.microsoft.com/en-us/edge" target="_blank"><img src="<?= $moduleDir; ?>/img/cms/ed_logo.jpg"></a>
        <a href="http://www.mozilla.org/firefox/" target="_blank"><img src="<?= $moduleDir; ?>/img/cms/ff_logo.jpg"></a>
        <a href="http://www.opera.com/browser/" target="_blank"><img src="<?= $moduleDir; ?>/img/cms/opera_logo.jpg"></a>
        <a href="http://www.google.ru/chrome" target="_blank"><img src="<?= $moduleDir; ?>/img/cms/chrome_logo.jpg"></a>
    </p>
</div>

<?php $this->endBody(); ?>

</body>
</html>

<?php $this->endPage(); ?>
