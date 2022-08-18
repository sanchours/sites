<?php

namespace skewer\build\Page\News;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    public static $aGroups = [
        'news' => 'news.groups_news',
        'news_in_column' => 'news.groups_news_in_column',
    ];

    public static $iGroupSortIndex = 10;

    /** {@inheritdoc} */
    public function getList()
    {
        return [
            // новости в центре
            [
                'name' => 'titleOnMain',
                'group' => 'news',
                'section' => 'main',
                'title' => 'news.title_on_main',
            ],
            [
                'name' => 'onPage',
                'group' => 'news',
                'title' => 'news.on_page_main',
                'section' => 'main',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'section_all',
                'group' => 'news',
                'title' => 'news.section_all',
            ],
            [
                'name' => 'onMainShowType',
                'group' => 'news',
                'title' => 'news.on_main_show_type',
                'section' => 'main',
                'editor' => 'select',
                'settings' => [
                    'emptyStr' => false,
                ],
                'options' => [
                    'list' => 'news.show_type_list',
                    'column' => 'news.show_type_columns',
                    'carousel' => 'news.show_type_carousel',
                ],
            ],

            // новости в колонке
            [
                'name' => 'titleOnMain',
                'group' => 'news_in_column',
                'title' => 'news.title_on_main',
            ],
            [
                'name' => 'onPage',
                'group' => 'news_in_column',
                'title' => 'news.on_page_main',
                'editor' => 'int',
                'section' => 'main',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'onPage',
                'group' => 'news_in_column',
                'title' => 'news.on_page',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'section_all',
                'group' => 'news_in_column',
                'title' => 'news.section_all',
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
