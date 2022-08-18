<?php

namespace skewer\build\Adm\Order;

use skewer\components\config\InstallPrototype;
use skewer\components\i18n\ModulesParams;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        ModulesParams::setParams('order', 'status_paid_content', 'ru', \Yii::t('data/order', 'status_paid_content', [], 'ru'));
        ModulesParams::setParams('order', 'title_status_paid', 'ru', \Yii::t('data/order', 'title_status_paid', [], 'ru'));
        ModulesParams::setParams('order', 'content_change_order', 'ru', \Yii::t('data/order', 'content_change_order', [], 'ru'));
        ModulesParams::setParams('order', 'title_change_order', 'ru', \Yii::t('data/order', 'title_change_order', [], 'ru'));
        ModulesParams::setParams('order', 'status_paid_content', 'en', \Yii::t('data/order', 'status_paid_content', [], 'en'));
        ModulesParams::setParams('order', 'title_status_paid', 'en', \Yii::t('data/order', 'title_status_paid', [], 'en'));
        ModulesParams::setParams('order', 'content_change_order', 'en', \Yii::t('data/order', 'content_change_order', [], 'en'));
        ModulesParams::setParams('order', 'title_change_order', 'en', \Yii::t('data/order', 'title_change_order', [], 'en'));

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}//class
