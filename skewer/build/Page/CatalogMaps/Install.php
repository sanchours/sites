<?php

namespace skewer\build\Page\CatalogMaps;

use skewer\base\ft\Editor;
use skewer\base\section\Parameters;
use skewer\base\section\params;
use skewer\base\section\Template;
use skewer\base\site\Type;
use skewer\base\SysVar;
use skewer\components\catalog\Attr;
use skewer\components\catalog\Card;
use skewer\components\catalog\model\FieldTable;
use skewer\components\config\InstallPrototype;
use yii\base\UserException;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        if (!Type::hasCatalogModule()) {
            throw new UserException('Не установлен модуль каталога');
        }
        // Добавить поля в карточку
        $this->addNewFieldsInCard();

        // Добавить параметры в разделы
        $this->addParams();

        return true;
    }

    // func

    public function uninstall()
    {
        Parameters::removeByGroup(Module::group_params_module);

        return true;
    }

    // func

    private function addNewFieldsInCard()
    {
        $oFieldMap = FieldTable::getNewRow();
        $oFieldMap->name = 'map';
        $oFieldMap->title = \Yii::t('data/catalog', 'field_map_title', [], SysVar::get('language'));
        $oFieldMap->type = 'int';
        $oFieldMap->editor = Editor::MAP_SINGLE_MARKER;
        $oFieldMap->entity = Card::get(Card::DEF_BASE_CARD)->id;
        $oFieldMap->link_id = 7;
        $oFieldMap->save();

        $oFieldMap->setAttr(Attr::ACTIVE, 0);
        $oFieldMap->setAttr(Attr::SHOW_IN_TAB, 1);
        $oFieldMap->setAttr(Attr::SHOW_IN_DETAIL, 1);
        $oFieldMap->setAttr(Attr::SHOW_IN_LIST, 0);

        Card::build($oFieldMap->entity);
    }

    private function addParams()
    {
        $aSections = Template::getSubSectionsByTemplate(\Yii::$app->sections->tplNew());

        $aSections[] = \Yii::$app->sections->tplNew();

        foreach ($aSections as $item) {
            Parameters::setParams($item, Module::group_params_module, Parameters::groupName, 'сatalogMaps.param_groupTitle');
            Parameters::setParams($item, Module::group_params_module, Parameters::object, Module::getNameModule());
            Parameters::setParams($item, Module::group_params_module, Parameters::layout, 'content');
            Parameters::setParams($item, Module::group_params_module, 'sSourceSections', null, '\skewer\components\catalog\Section::getList()', 'сatalogMaps.param_sourceSections', params\Type::paramMultiSelect);
            Parameters::setParams($item, Module::group_params_module, 'iMapId', null, null, 'сatalogMaps.param_map', params\Type::paramMapListMarkers);
            Parameters::setParams($item, Module::group_params_module, 'bShowModification', true, null, 'сatalogMaps.param_bShowModification', params\Type::paramCheck);
            Parameters::setParams($item, Module::group_params_module, 'sTitleBlock', '', null, 'сatalogMaps.param_sTitleBlock', params\Type::paramString);
            Parameters::setParams($item, Module::group_params_module, Parameters::excludedGroup);
        }
    }
}//class
