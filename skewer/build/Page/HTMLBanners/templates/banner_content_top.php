<?php

use skewer\components\design\Design;

?>

<?php if ((isset($dataProvider)) and (count($dataProvider) > 0)): ?>
    <?php foreach ($dataProvider as $item): ?>
        <div class="b-bannercenter"<?php if (Design::modeIsActive()): ?> sklabel="<?= $_objectId; ?>"<?php endif; ?>>
            <?= $item['content']; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>