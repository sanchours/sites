<?php

namespace skewer\build\Tool\Payments;

use skewer\base\SysVar;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\ar\Order;
use skewer\components\catalog\Card;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    /**
     * @throws \Exception
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public function install()
    {
        ar\Params::rebuildTable();
        ar\PayPalPayments::rebuildTable();
        ar\SberbankPayments::rebuildTable();
        ar\Params::getParam('robokassa', 'active', true);
        ar\Params::getParam('payanyway', 'active', true);
        ar\Params::getParam('paypal', 'active', true);
        ar\Params::getParam(UkassaPayment::PAYMENT_TYPE, 'active', true);
        ar\Params::getParam('sberbank', 'active', true);

        Order::rebuildTable();

        $resQuery = \Yii::$app->db->createCommand('SHOW COLUMNS FROM `' . Goods::getTableName() . '` LIKE "payment_object"')->queryOne();
        if ($resQuery == false) {
            Goods::rebuildTable();
        }

        $oEntityRow = \skewer\components\catalog\Entity::get(Card::DEF_BASE_CARD);
        if ($oEntityRow) {
            $resQuery = \Yii::$app->db->createCommand('SHOW COLUMNS FROM `co_' . Card::DEF_BASE_CARD . '` LIKE "payment_object"')->queryOne();
            if ($resQuery == false) {
                //устанавливается русскоязычный title из-за отсутствия возможности
                //корректно обновить словари на момент установки модуля
                $aDataField =
                    [
                        'name' => 'payment_object',
                        'title' => 'Признак предмета расчета',
                        'group' => 0,
                        'editor' => 'paymentObject',
                        'attr' => [
                            \skewer\components\catalog\Attr::ACTIVE => 0,
                            \skewer\components\catalog\Attr::SHOW_IN_LIST => 0,
                            \skewer\components\catalog\Attr::SHOW_IN_DETAIL => 0,
                        ],
                        'prohib_del' => 1,
                        'no_edit' => 1,
                    ];
                \skewer\components\catalog\Generator::createField($oEntityRow->id, $aDataField);
                $oEntityRow->updCache();
            }
            SysVar::set(Card::PREFIX_PAYMENT_OBJECT_NAME . Card::DEF_BASE_CARD, 'commodity');
        }

        return true;
    }

    public function uninstall()
    {
        Order::rebuildTable();
        SysVar::del(Card::PREFIX_PAYMENT_OBJECT_NAME . Card::DEF_BASE_CARD);

        return true;
    }
}
