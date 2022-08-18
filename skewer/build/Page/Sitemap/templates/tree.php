<?php

/** @var array $aBranches */
/** @var array $aAllSections */
?>


<div class="b-sitemap">

    <ul class="level-1">
        <?foreach ($aAllSections as $branch): ?>

            <?= Yii::$app->view->renderPhpFile(
    __DIR__ . '/branch.php',
    [
                    'aSections' => $branch,
                    'level' => 1,
                ]
);
            ?>

        <?php endforeach; ?>
    </ul>

</div>


