<?php
/** @var $this \yii\web\View */
use skewer\build\Page\CategoryViewer\templates\standard1;
use skewer\components\design\Design;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var $list array */
/** @var $title string */

standard1\Asset::register(Yii::$app->view, ['aActiveSections' => ArrayHelper::getColumn($list, 'id')]);

?>

<div class="b-category <?php if (!empty($html_class)): ?><?= $html_class ?> <?php endif; ?>">
    <?php if ($title): ?>
        <h2><?= $title ?></h2>
    <?php endif; ?>
    <div class="category__items">
        <?php  foreach ($list as $aItem): ?>

            <div class="category__item category__item-<?=$aItem['id']; ?> ">
                <div class="category__inner">
                    <div class="category__imgbox">
                        <a href="<?=$aItem['href']; ?>">
                            <img alt="<?= Html::encode(ArrayHelper::getValue($aItem, 'img.alt_title', '')); ?>"
                                 title="<?= Html::encode(ArrayHelper::getValue($aItem, 'img.title', '')); ?>"
                                 src="<?= ArrayHelper::getValue($aItem, 'img.images_data.preview.file', Design::get('modules.category.gal', 'noimage', 'skewer\build\Page\CategoryViewer\Asset')); ?>" />
                        </a>
                    </div>

                    <div class="category__title"><a href="<?=$aItem['href']; ?>"><?=$aItem['title']; ?></a></div>

                    <?php if (!empty($aItem['description'])): ?>
                        <div class="category__text b-editor b-editor-nobot"><?=$aItem['description']; ?></div>
                    <?endif; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>
