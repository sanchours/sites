<?php

use skewer\build\Tool\Slider\Api;
use skewer\components\config\PatchPrototype;

class Patch66620 extends PatchPrototype
{
    public $sDescription = 'Добавление колонки link_target в таблицы banners_slides и banners_main';

    public function execute()
    {
        Yii::$app->db
            ->createCommand('ALTER TABLE `banners_slides` ADD `link_target` VARCHAR(255) DEFAULT "' . Api::TARGET_TYPE_BLANK . '" AFTER `slide_link`;')
            ->execute();

        Yii::$app->db
            ->createCommand('ALTER TABLE `banners_main` ADD `link_target` VARCHAR(255) DEFAULT "' . Api::TARGET_TYPE_BLANK . '" AFTER `scroll`;')
            ->execute();
    }
}
