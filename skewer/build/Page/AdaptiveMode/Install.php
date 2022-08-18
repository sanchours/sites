<?php

namespace skewer\build\Page\AdaptiveMode;

use skewer\base\section;
use skewer\build\Design\Zones;
use skewer\components\config\InstallPrototype;
use skewer\components\design\model\Params as CssParam;
use skewer\components\i18n\Languages;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        /** Массив имён всех используемых языков на сайте */
        $aLangs = Languages::getAllActiveNames() + ['ru' => 'ru'];

        /* Добавление группы параметров блока режима адаптивности и кнопки адаптивного меню */
        Api::setSectionParam([
                                 'name' => Zones\Api::layoutTitleName,
                                 'group' => Api::ADP_MENU_BLOCK_MODE,
                                 'value' => 'Адаптивный режим и кнопка адаптивного меню',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => Zones\Api::layoutParamName,
                                 'group' => Api::ADP_MENU_BLOCK_MODE,
                                 'value' => 'head',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => 'object',
                                 'group' => Api::ADP_MENU_BLOCK_MODE,
                                 'value' => $this->getModuleName(),
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);

        /* Добавление группы параметров блока верхнего меню адаптивного меню */
        Api::setSectionParam([
                                 'name' => Zones\Api::layoutTitleName,
                                 'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
                                 'value' => 'Разделы верхнего меню',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => Zones\Api::layoutParamName,
                                 'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
                                 'value' => Api::ADP_MENU_LAYOUT_NAME,
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => 'object',
                                 'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
                                 'value' => 'Menu',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => 'html_class',
                                 'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
                                 'value' => 'b-sidebar-menu--topmenu',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);

        Api::setSectionParam([
                                  'name' => 'parentSection',
                                  'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
                                  'value' => 'topMenu',
                                  'parent' => \Yii::$app->sections->tplNew(),
                                  'access_level' => section\params\Type::paramServiceSection,
                              ]);
        Api::setSectionParam([
                                 'name' => 'templateFile',
                                 'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
                                 'value' => 'adaptive-menu.twig',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
            'name' => 'openAll',
            'group' => Api::ADP_MENU_BLOCK_TOP_MENU,
            'value' => '1',
            'parent' => \Yii::$app->sections->tplNew(),
        ]);

        /* Добавление группы параметров блока левого меню адаптивного меню */
        Api::setSectionParam([
                                 'name' => Zones\Api::layoutTitleName,
                                 'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'value' => 'Разделы левого меню',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => Zones\Api::layoutParamName,
                                 'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'value' => Api::ADP_MENU_LAYOUT_NAME,
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => 'object',
                                 'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'value' => 'Menu',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                  'name' => 'parentSection',
                                  'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                  'value' => 'leftMenu',
                                  'parent' => \Yii::$app->sections->tplNew(),
                                  'access_level' => section\params\Type::paramServiceSection,
                              ]);
        Api::setSectionParam([
                                 'name' => 'templateFile',
                                 'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'value' => 'adaptive-menu.twig',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => 'openAll',
                                 'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'value' => '1',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);
        Api::setSectionParam([
                                 'name' => 'html_class',
                                 'group' => Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'value' => 'b-sidebar-menu--leftmenu',
                                 'parent' => \Yii::$app->sections->tplNew(),
                             ]);

        // Добавление новой области страницы для адаптивного меню
        Api::setSectionParam([
                                 'name' => Api::ADP_MENU_LAYOUT_NAME,
                                 'group' => Zones\Api::layoutGroupName,
                                 'value' => 'headtext1,headtext2,' . Api::ADP_MENU_BLOCK_TOP_MENU . ',' . Api::ADP_MENU_BLOCK_LEFT_MENU,
                                 'title' => 'Адаптивное меню',
                                 'parent' => \Yii::$app->sections->root(),
                             ]);
        self::addParamCatalogMenu($aLangs);

        // сброс минимальной ширины для сайта
        CssParam::updateAll(['value' => '320px'], ['name' => 'page.min-width']);

        /* Активация режима адаптивности и вывод кнопки адаптивного меню во всех разделах сайта */
        $this->removeObjectFromLayouts(Api::ADP_MENU_BLOCK_MODE);
        $aParamsHead = section\Parameters::getList()
            ->group(Zones\Api::layoutGroupName)
            ->name('head')
            ->get();

        foreach ($aParamsHead as $oParamHead) {
            if ($oParamHead->access_level == section\params\Type::paramSystem) {
                // если нет адаптивного модуля в выводе - добавить
                if (mb_strpos($oParamHead->show_val, Api::ADP_MENU_BLOCK_MODE) === false) {
                    $oParamHead->show_val = rtrim(Api::ADP_MENU_BLOCK_MODE . ',' . $oParamHead->show_val, ',');
                    $oParamHead->save();
                }
            }
        }

        return true;
    }

    // func

    public static function addParamCatalogMenu($aLangs)
    {
        /* Добавление группы параметров блока каталожного меню адаптивного меню */
        Api::setSectionParam([
            'name' => Zones\Api::layoutTitleName,
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => 'Разделы каталожного меню',
            'parent' => \Yii::$app->sections->tplNew(),
        ]);
        Api::setSectionParam([
            'name' => Zones\Api::layoutParamName,
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => Api::ADP_MENU_LAYOUT_NAME,
            'parent' => \Yii::$app->sections->tplNew(),
        ]);
        Api::setSectionParam([
            'name' => 'object',
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => 'Menu',
            'parent' => \Yii::$app->sections->tplNew(),
        ]);
        Api::setSectionParam([
            'name' => 'html_class',
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => 'b-sidebar-menu--catalog',
            'parent' => \Yii::$app->sections->tplNew(),
        ]);

        Api::setSectionParam([
            'name' => 'parentSection',
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => 'leftMenu',
            'parent' => \Yii::$app->sections->tplNew(),
            'access_level' => section\params\Type::paramServiceSection,
        ]);

        Api::setSectionParam([
            'name' => 'templateFile',
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => 'adaptive-menu.twig',
            'parent' => \Yii::$app->sections->tplNew(),
        ]);
        Api::setSectionParam([
            'name' => 'openAll',
            'group' => Api::ADP_MENU_BLOCK_CATALOG_MENU,
            'value' => '1',
            'parent' => \Yii::$app->sections->tplNew(),
        ]);
    }

    public function uninstall()
    {
        // восстановление минимальной ширины для сайта
        CssParam::updateAll(['value' => '980px'], ['name' => 'page.min-width']);

        $this->removeObjectFromLayouts(Api::ADP_MENU_BLOCK_MODE);

        // Удалить область страницы адаптивного меню
        section\Parameters::removeByName(Api::ADP_MENU_LAYOUT_NAME, Zones\Api::layoutGroupName);

        // Удалить группы параметров адаптивного меню
        section\Parameters::removeByGroup(Api::ADP_MENU_BLOCK_MODE);
        section\Parameters::removeByGroup(Api::ADP_MENU_BLOCK_TOP_MENU);
        section\Parameters::removeByGroup(Api::ADP_MENU_BLOCK_LEFT_MENU);
        section\Parameters::removeByGroup(Api::ADP_MENU_BLOCK_CATALOG_MENU);

        return true;
    }

    // func
}//class
