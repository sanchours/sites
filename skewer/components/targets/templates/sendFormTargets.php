<?php

namespace skewer\components\targets\templates;

/* @var array $googleReachGoal */
/* @var array $yandexReachGoal */

?>


<?php if (isset($yandexReachGoal)): ?>

    <script language="JavaScript">
        <?= \Yii::$app->view->renderPhpFile(
    __DIR__ . '/yandex.php',
    [
                'name' => $yandexReachGoal['target'],
            ]
);
        ?>
    </script>

<?endif; ?>


<?php if (isset($googleReachGoal)): ?>

    <script language="JavaScript">
        <?= \Yii::$app->view->renderPhpFile(
            __DIR__ . '/google.php',
            [
                'category' => $googleReachGoal['category'],
                'name' => $googleReachGoal['target'],
            ]
        );
        ?>
    </script>

<?endif; ?>

