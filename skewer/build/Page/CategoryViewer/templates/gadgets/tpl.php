<?php

use skewer\build\Page\CategoryViewer\templates\gadgets;
use skewer\components\design\Design;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var $list array */
/** @var $title string */

gadgets\Asset::register(Yii::$app->view, ['aActiveSections' => ArrayHelper::getColumn($list, 'id')]);

?>

<div class="b-category-3 <?php if (!empty($html_class)): ?><?= $html_class ?> <?php endif; ?>">
    <?php if ($title): ?>
        <h2><?= $title ?></h2>
    <?php endif; ?>
    <?php  foreach ($list as $aItem): ?>
        <div class="category__item section_<?=$aItem['id']; ?> category__item-<?=$aItem['designParams']['block;width_koef']; ?>x <?php if (!empty($aItem['designParams']['block;use_special_class'])):?>category__special<?php endif; ?>">
            <div class="category__wrap">
                <div class="category__imgbox">
                    <img alt="<?= Html::encode(ArrayHelper::getValue($aItem, 'img.alt_title', '')); ?>"
                         title="<?= Html::encode(ArrayHelper::getValue($aItem, 'img.title', '')); ?>"
                         src="<?= ArrayHelper::getValue($aItem, 'img.images_data.preview.file', Design::get('modules.category.gal', 'noimage', 'skewer\build\Page\CategoryViewer\Asset')); ?>" />
                </div>

                <div class="category__link"><a href="<?=$aItem['href']; ?>"><?=$aItem['title']; ?></a></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>