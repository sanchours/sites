<?php
    /**
    * Structure: Dict
    * @var array() $aNameField - наименования строк
    * @var int $id - not displayed
    * @var string $title Название
    * @var int $priority priority - not displayed
    * @var string $alias Техническое имя - not displayed
    */
?>
<h1><?=  $title; ?></h1>


    <?php  if ($title): ?>
        <div><?= $aNameField["title"] ?> : <?= $title; ?></div>
    <?php  endif; ?>

<p class="dict__linkback">
    <a rel="nofollow" href="#" onclick="history.go(-1);return false;">
        <?=  \Yii::t('page', 'back'); ?>
    </a>
</p>
