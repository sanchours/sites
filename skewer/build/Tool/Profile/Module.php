<?php

namespace skewer\build\Tool\Profile;

use skewer\base\SysVar;
use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;

/**
 * Класс настройки Личного кабинета
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    private $default_min_onpage = 10;
    private $default_type = 'List';

    protected function actionInit()
    {
        $this->render(new view\Index([
            'isSys' => CurrentAdmin::isSystemMode(),
        ]));
    }

    protected function actionSaveWishListParams()
    {
        $Data = $this->getInData();

        SysVar::set('WishList.Enable', !empty($Data['enableModule']) ? $Data['enableModule'] : false);
        SysVar::set('WishList.OnPage', !empty($Data['OnPage']) ? $Data['OnPage'] : $this->default_min_onpage);
        SysVar::set('WishList.ShowMod', !empty($Data['ShowMod']) ? $Data['ShowMod'] : $this->default_type);

        $this->actionInit();
    }

    /**
     * Настройка отображения модуля списка желаний.
     */
    protected function actionWishList()
    {
        $this->render(new view\Form([
            'values' => [
                'enableModule' => SysVar::get('WishList.Enable'),
                'OnPage' => SysVar::get('WishList.OnPage'),
                'ShowMod' => SysVar::get('WishList.ShowMod'),
            ],
        ]));
    }
}//class
