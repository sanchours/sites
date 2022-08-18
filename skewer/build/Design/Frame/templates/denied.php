<?php

use skewer\components\design\Design;

/*
 * @var int $sessionId
 * @var string $layoutMode
 * @var string $moduleDir
 * @var string $dictVals
 * @var string $ver
 * @var string $lang
 *
 * @var \yii\web\View $this
 */

//$bundle = skewer\build\Design\Frame\AssetIndex::register($this);

$this->beginPage();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html" xml:lang="ru">
<head>
    <meta charset="utf-8"  content="text/html"/>
    <title>Дизайнерский режим</title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <link rel="shortcut icon" href="<?= Design::get('page', 'favicon', 'skewer\build\Page\Main\Asset'); ?>" type="image/png" />
    <link rel="stylesheet" type="text/css" href="<?= $moduleDir; ?>/css/frame.css" media="all" />
</head>
<body>
<div class="b-main-denied">
    <h1>Доступ к дизайнерскому режиму запрещен для данной политики</h1>
    <p>Обратитесь к администратору сайта в случае необходимости доступа.</p>
    <p>Вы можете перейти в <a href="/admin/">систему администрирования сайта</a>.</p>
</div>
</body>
</html>
