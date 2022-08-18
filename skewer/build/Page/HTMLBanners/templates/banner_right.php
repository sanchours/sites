<?php use skewer\components\design\Design;

if ((isset($dataProvider)) and (count($dataProvider) > 0)): ?>
    <?php foreach ($dataProvider as $item): ?>
        <div class="b-bannerright"<?php if (Design::modeIsActive()): ?> sklabel="<?= $_objectId; ?>"<?php endif; ?>>
            <?= $item['content']; ?>
        </div>
    <?endforeach; ?>
<?endif; ?>