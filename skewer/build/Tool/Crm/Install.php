<?php

namespace skewer\build\Tool\Crm;

use skewer\base\SysVar;
use skewer\build\Tool\Crm\models\DealEvent;
use skewer\build\Tool\Crm\models\DealType;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        if (!$this->tableExists(DealType::tableName())) {
            \Yii::$app->db->createCommand('
                CREATE TABLE `crm_deal_types` (
                  `id` int(11) NOT NULL,
                  `name_site` varchar(128),
                  `name_crm` varchar(128) NOT NULL,
                  `active` tinyint(4) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ')
                ->execute();
        }
        if (!$this->tableExists(DealEvent::tableName())) {
            \Yii::$app->db->createCommand('
              CREATE TABLE `crm_deal_events` (
              `id` int(11) NOT NULL,
              `title_site` varchar(128),
              `title_crm` varchar(128) NOT NULL,
              `from` timestamp NULL DEFAULT NULL,
              `to` timestamp NULL DEFAULT NULL,
              `active` tinyint(1) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ')
                ->execute();
        }

        if (!$this->tableExists('crm_link_form')) {
            \Yii::$app->db->createCommand('
              CREATE TABLE `crm_link_form` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `crm_field_alias` varchar(128) NOT NULL,
              `form_id` int(11) NOT NULL,
              `field_id` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ')
                ->execute();
        }

        SysVar::set(Api::CRM_SYSVAR_INTEGRATION, Api::CRM_EMAIL_INTEGRATION);

        return true;
    }

    // func

    public function uninstall()
    {
        if ($this->tableExists(DealType::tableName())) {
            \Yii::$app->db->createCommand('DROP TABLE crm_deal_types')->execute();
        }

        if ($this->tableExists(DealEvent::tableName())) {
            \Yii::$app->db->createCommand('DROP TABLE crm_deal_events')->execute();
        }

        if ($this->tableExists('crm_link_form')) {
            \Yii::$app->db->createCommand('DROP TABLE crm_link_form')->execute();
        }

        SysVar::del(Api::CRM_SYSVAR_DOMAIN);
        SysVar::del(Api::CRM_SYSVAR_TOKEN);

        return true;
    }

    // func
}//class
