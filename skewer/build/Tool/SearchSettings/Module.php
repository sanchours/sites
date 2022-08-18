<?php

namespace skewer\build\Tool\SearchSettings;

use skewer\base\section\Parameters;
use skewer\base\site\Type;
use skewer\base\SysVar;
use skewer\build\Page\CatalogViewer;
use skewer\build\Tool;
use skewer\components\search;

/**
 * Модуль Настройки поиска
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $iType = (int) SysVar::get('Search.default_type');

        $iCountSearch = Parameters::getValByName(\Yii::$app->sections->search(), 'content', 'onPage', true);

        $this->render(new Tool\SearchSettings\view\Index([
            'bHasCatalogModule' => Type::hasCatalogModule(),
            'aTypeList' => search\Type::getTypeList(),
            'aSearchTypeList' => search\Type::getSearchTypeList(),
            'aTemplates' => CatalogViewer\State\ListPage::getTemplates(),
            'aValue' => [
                'type' => $iType,
                'search_type' => SysVar::get('Search.search_type'),
                'showSort' => SysVar::get('Search.showSort'),
                'tpl_name' => SysVar::get('Search.CatalogListTemplate'),
                'hidePlaceHolder' => SysVar::get('Search.hidePlaceHolder'),
                'countSearch' => $iCountSearch,
            ],
        ]));
    }

    /**
     * Сохранение.
     */
    protected function actionSave()
    {
        SysVar::set('Search.default_type', $this->getInDataVal('type'));
        if (Type::hasCatalogModule()) {
            SysVar::set('Search.search_type', (int) $this->getInDataVal('search_type'));

            SysVar::set('Search.CatalogListTemplate', $this->getInDataVal('tpl_name'));
            $this->setParams('sCatalogListTemplate', $this->getInDataVal('tpl_name'));
            $this->setParams('search_type', (int) $this->getInDataVal('search_type'));
        }

        SysVar::set('Search.hidePlaceHolder', (bool) $this->getInDataVal('hidePlaceHolder'));

        Parameters::setParams(\Yii::$app->sections->search(), 'content', 'onPage', $this->getInDataVal('countSearch'));

        $this->actionInit();
    }

    /**
     * Устанавливает параметр во все поисковые разделы.
     *
     * @param $sParamName
     * @param $value
     */
    private function setParams($sParamName, $value)
    {
        $aSearchParam = Parameters::getListByModule('Search', 'content');

        if ($aSearchParam) {
            foreach ($aSearchParam as $iParam) {
                Parameters::setParams($iParam, 'content', $sParamName, $value);
            }
        }
    }
}
