<?php

namespace skewer\build\Tool\DeliveryPayment;

use skewer\base\site\Type;
use skewer\build\Tool\DeliveryPayment\models\TypeDelivery;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\components\auth\Policy;
use skewer\components\config\InstallPrototype;
use skewer\helpers\Transliterate;

/**
 * Class Install.
 */
class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public function install()
    {
        \Yii::$app->db->createCommand('CREATE TABLE `orders_delivery` (
                                          id INT(11) NOT NULL AUTO_INCREMENT,
                                          title VARCHAR(255) NOT NULL,
                                          alias VARCHAR(255) NOT NULL,
                                          active INT(1) NOT NULL,
                                          min_cost INT(11) NULL,
                                          free_shipping INT(1) NOT NULL,
                                          price INT(11) NULL,
                                          address VARCHAR(255) NOT NULL, 
                                          coord_deliv_costs INT(1) NOT NULL,
                                          priority INT(11) NOT NULL,
                                          PRIMARY KEY (id)
                                      )')->execute();

        $aPresetTypeDelivery = [
            [
                'title' => \Yii::t('order', 'tp_deliv_1', [], \Yii::$app->language),
                'alias' => Transliterate::generateAlias(\Yii::t('order', 'tp_deliv_1', [], \Yii::$app->language)),
            ],
            [
                'title' => \Yii::t('order', 'tp_deliv_2', [], \Yii::$app->language),
                'alias' => Transliterate::generateAlias(\Yii::t('order', 'tp_deliv_2', [], \Yii::$app->language)),
            ],
            [
                'title' => \Yii::t('order', 'tp_deliv_3', [], \Yii::$app->language),
                'alias' => Transliterate::generateAlias(\Yii::t('order', 'tp_deliv_3', [], \Yii::$app->language)),
            ],
        ];

        \Yii::$app->db->createCommand('CREATE TABLE `orders_payment` (
                                          id INT(11) NOT NULL AUTO_INCREMENT,
                                          title VARCHAR(255) NOT NULL,
                                          payment VARCHAR(64) NOT NULL,
                                          alias VARCHAR(255) NOT NULL,
                                          active INT(1) NOT NULL,
                                          message VARCHAR(255) NOT NULL,
                                          priority INT(11) NOT NULL,
                                          PRIMARY KEY (id)
                                      )')->execute();

        foreach ($aPresetTypeDelivery as $aItem) {
            $aItem['priority'] = Api::getMaxPriority(TypeDelivery::tableName());
            $oItem = TypeDelivery::getNewRow();
            $oItem->saveWithLink($aItem);
        }

        $aPresetTypePayment = [
            [
                'title' => \Yii::t('order', 'tp_pay_1', [], \Yii::$app->language),
                'alias' => Transliterate::generateAlias(\Yii::t('order', 'tp_pay_1', [], \Yii::$app->language)),
            ],
            [
                'title' => \Yii::t('order', 'tp_pay_2', [], \Yii::$app->language),
                'alias' => Transliterate::generateAlias(\Yii::t('order', 'tp_pay_2', [], \Yii::$app->language)),
            ],
            [
                'title' => \Yii::t('order', 'tp_pay_3', [], \Yii::$app->language),
                'alias' => Transliterate::generateAlias(\Yii::t('order', 'tp_pay_3', [], \Yii::$app->language)),
            ],
        ];

        foreach ($aPresetTypePayment as $aItem) {
            $aItem['priority'] = Api::getMaxPriority(TypePayment::tableName());
            $oItem = TypePayment::getNewRow($aItem);
            $oItem->save();
        }

        //создаем таблицу для связи многие-ко-многим для TypeDelivery и TypePayment
        \Yii::$app->db->createCommand('CREATE TABLE `orders_delivery_payment` (
                                          id INT(11) NOT NULL AUTO_INCREMENT,
                                          delivery_id INT(11) NOT NULL,
                                          payment_id INT(11) NOT NULL,
                                          PRIMARY KEY (id)
                                      )')->execute();

        \Yii::$app->db->createCommand(
            'ALTER TABLE `orders_delivery_payment`
                                                  ADD INDEX (delivery_id);'
        )->execute();

        Policy::addModule(Policy::POLICY_ADMIN_USERS, $this->getModuleName(), $this->config->getTitle());

        return true;
    }

    /**
     * @throws \skewer\components\config\UpdateException
     *
     * @return bool
     */
    public function uninstall()
    {
        if (Type::isShop()) {
            $this->fail('Нельзя удалить магазин');
        }

        return true;
    }
}
