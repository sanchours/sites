<?php

namespace skewer\build\Tool\Order;

use skewer\base\ft\Cache;
use skewer\base\ft\Fnc;
use skewer\base\site;
use skewer\base\SysVar;
use skewer\build\Design\Zones\Api;
use skewer\build\Tool\LeftList\Group;
use skewer\components\auth\Policy;
use skewer\components\catalog\Attr;
use skewer\components\catalog\Card;
use skewer\components\catalog\Generator;
use skewer\components\catalog\model\FieldGroupRow;
use skewer\components\catalog\model\FieldGroupTable;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        $idZone = Api::getZoneIdByName(Group::CONTENT, \Yii::$app->sections->main());
        Api::addLabel(\skewer\components\catalog\Api::LANG_GROUP_NAME, $idZone, \Yii::$app->sections->main());

        if (!Fnc::tableHasField('users', 'reg_date')) {
            /*Добавим reg_date в таблицу Users*/
            \Yii::$app->db->createCommand('ALTER TABLE `users` ADD COLUMN `reg_date` datetime NOT NULL')->execute();
        }

        SysVar::set('site_type', site\Type::shop);

        $this->addFieldsInBaseCard();

        $iIdSectionOrderForm = \Yii::$app->sections->getValue('orderForm');
        \Yii::$app->db->createCommand("DELETE FROM `parameters` WHERE `group`='forms' and `name`='FormId' and `parent` =" . $iIdSectionOrderForm)->execute();
        \Yii::$app->db->createCommand('INSERT INTO `parameters`(`id`, `parent`, `group`, `name`, `value`, `title`, `access_level`, `show_val`) 
                                             VALUES (null,' . $iIdSectionOrderForm . ",'order','objectAdm','Order','',0,'')")->execute();

        Policy::addModule(Policy::POLICY_ADMIN_USERS, $this->getModuleName(), $this->config->getTitle());

        return true;
    }

    // func

    public function uninstall()
    {
        if (site\Type::isShop()) {
            $this->fail('Нельзя удалить магазин');
        }

        return true;
    }

    // func

    private function addFieldsInBaseCard()
    {
        /** @var FieldGroupRow $oGroup */
        $oGroup = FieldGroupTable::findOne(['name' => 'controls']);

        $oBaseCard = Cache::get(Card::DEF_BASE_CARD);

        $aFieldData = [
            'name' => 'fastbuy',
            'title' => \Yii::t('data/catalog', 'field_fastbuy_title', [], \Yii::$app->language),
            'group' => $oGroup->id,
            'editor' => 'check',
            'def_value' => '',
            'attr' => [Attr::ACTIVE => 0],
        ];

        Generator::createField($oBaseCard->getEntityId(), $aFieldData);
    }
}//class
