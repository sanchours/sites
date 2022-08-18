<?php

use skewer\components\design\Design;

/**
 * @var \yii\web\View
 */
$bundle = \skewer\components\ext\Asset::register($this);

$this->beginPage();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html" xml:lang="ru">
<head>
    <meta charset="utf-8"  content="text/html"/>
    <title>Дизайнерский режим</title>
    <link rel="shortcut icon" href="<?= Design::get('page', 'favicon', 'skewer\build\Page\Main\Asset'); ?>" type="image/png" />
    <?php $this->head(); ?>

</head>
<body>
<?php $this->beginBody(); ?>
<div id="js_admin_preloader" class="admin-preloader">
    <img src="<?= $this->getAssetManager()->getBundle(\skewer\build\Cms\Frame\Asset::className())->baseUrl; ?>/img/preloader.gif" />
</div>
<?php $this->endBody(); ?>
</body>
</html>

<?php $this->endPage(); ?>