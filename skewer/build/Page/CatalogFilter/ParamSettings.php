<?php

namespace skewer\build\Page\CatalogFilter;

use skewer\components\catalog;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    public static $aGroups = [
        'CatalogFilter' => 'catalogFilter.groups_catalog_filter',
    ];

    public static $iGroupSortIndex = 50;

    /** {@inheritdoc} */
    public function getList()
    {
        return [
            [
                'name' => 'linkedSection',
                'group' => 'CatalogFilter',
                'title' => 'catalogFilter.section_cat_filter_form',
                'section' => 'main',
                'editor' => 'select',
                'options' => catalog\Section::getSearchList(),
            ],
        ];
    }

    /** {@inheritdoc} */
    public function saveData()
    {
    }

    public function getInstallationParam()
    {
        return [];
    }
}
