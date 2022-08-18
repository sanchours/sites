<?php

use yii\helpers\ArrayHelper;

/*
 * @var $this \yii\web\View
 * @var int $sectionId
 * @var array $_params_
 * @var string $favicon
 */

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= \Yii::$app->language; ?>" class="g-no-js">
<head>
    <meta charset="utf-8" />

    <meta http-equiv="Content-Type" content="text/html">

</head>


    <body>
                <?php
                $contentTpl = ArrayHelper::getValue($_params_, ['.layout', 'content_tpl', 0], 'base');
                echo $this->render('content/' . $contentTpl . '/tpl', $_params_);
                ?>



    </body>
</html>
