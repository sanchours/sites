<?php

namespace skewer\build\Tool\Payments\ar;

use skewer\base\orm;
use skewer\base\site\Layer;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;

/**
 * Class Params.
 */
class ParamRow extends orm\ActiveRecord
{
    public $id = 0;
    public $type = '';
    public $name = '';
    public $value = '';

    public function __construct()
    {
        $this->setTableName('payment_parameters');
        $this->setPrimaryKey('id');
    }

    /** {@inheritdoc} */
    public function afterSave($insert, $aData)
    {
        if (($this->name === 'active') && !$this->value) {
            if (\Yii::$app->register->moduleExists('DeliveryPayment', Layer::TOOL)) {
                // Сбросить деактивируемый тип оплаты в таблице типов оплат заказов
                TypePayment::updateAll(['payment' => ''], ['payment' => $aData['type']]);
            }
        }

        parent::afterSave($insert, $aData);
    }
}
