<?php

use skewer\base\section\models\TreeSection;
use skewer\build\Tool\Payments\UkassaPayment;
use skewer\components\config\PatchPrototype;
use skewer\components\i18n\models\ServiceSections;

class Patch101204 extends PatchPrototype
{
    public $sDescription = "Добавление поля для хранения id оплаты ЮКассы";

    public function execute()
    {
        $sUkassaOrderField = UkassaPayment::ORDER_FIELD;
        \Yii::$app->db->createCommand("
            ALTER TABLE `orders`
                ADD `$sUkassaOrderField` VARCHAR(255)
        ")->execute();

        \Yii::$app->db->createCommand("
            INSERT INTO `payment_parameters`(`type`,`name`,`value`)
                VALUES ('ukassa', 'active', '')
        ")->execute();
    }
}
