<?php

use skewer\build\Page\Copyright\Asset;
use skewer\build\Tool\Copyright\Api;

/**
 * @var \yii\web\View
 */
$aDisabledSections = Api::getSectionsWithDisabledCopyrightModule();

/* Подключение asset там где это требуется */
if (Api::getActivityModule() && !in_array(Yii::$app->router->sectionId, $aDisabledSections)) {
    Asset::register($this);
}

$sText = Api::getTemplatedText(Yii::$app->language, true);
?>

<div class="js_copyright_templatedText" style="display: none"><?=$sText; ?></div>