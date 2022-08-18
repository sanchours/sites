<?php

namespace skewer\build\Page\Articles;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    public static $aGroups = [
        'articles' => 'articles.groups_articles',
    ];

    public static $iGroupSortIndex = 20;

    /** {@inheritdoc} */
    public function getList()
    {
        return [
            [
                'name' => 'titleOnMain',
                'group' => 'articles',
                'title' => 'articles.titleOnMain',
            ],
            [
                'name' => 'onPage',
                'group' => 'articles',
                'title' => 'articles.on_column',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'section_all',
                'group' => 'articles',
                'title' => 'articles.section_all',
            ],
            [
                'name' => 'typeShowMain',
                'group' => 'articles',
                'title' => 'articles.type_show_main',
                'editor' => 'select',
                'options' => Api::getArray4TypeShow(),
                'default' => 'list',
                'settings' => [
                    'emptyStr' => false,
                ],
            ],
            [
                'name' => 'typeShowList',
                'group' => 'articles',
                'title' => 'articles.type_show_list',
                'editor' => 'select',
                'options' => Api::getArray4TypeShow(),
                'default' => 'list',
                'settings' => [
                    'emptyStr' => false,
                ],
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
