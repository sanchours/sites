<?php

namespace skewer\build\Page\Documents;

use skewer\build\Tool\Review\Api;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    public static $aGroups = [
        'Review' => 'review.groups_Review',
        'Review_in_column' => 'review.groups_Review_in_column',
    ];

    public static $iGroupSortIndex = 30;

    /** {@inheritdoc} */
    public function getList()
    {
        return [
            [
                'name' => 'titleOnMain',
                'group' => 'Review',
                'title' => 'news.title_on_main',
            ],
            [
                'name' => 'onPage',
                'group' => 'Review',
                'title' => 'review.show_on_page',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'onPageContent',
                'group' => 'Review',
                'title' => 'review.on_page',
                'editor' => 'int',
                'default' => '3',
                'settings' => [
                    'minValue' => 1,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'maxLen',
                'group' => 'Review',
                'title' => 'review.maxLen',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'section_id',
                'group' => 'Review',
                'title' => 'review.section_id_editor',
                'editor' => 'string',
            ],
            [
                'section' => 'all',
                'name' => 'typeShow',
                'group' => 'Review',
                'title' => 'review.field_template',
                'editor' => 'select',
                'options' => Api::getTypeShowReviews(),
                'default' => 'list',
            ],
            [
                'name' => 'titleOnMain',
                'group' => 'Review_in_column',
                'title' => 'news.title_on_main',
            ],
            [
                'name' => 'onPage',
                'group' => 'Review_in_column',
                'title' => 'review.show_on_page',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'onPageContent',
                'group' => 'Review_in_column',
                'title' => 'review.on_page',
                'editor' => 'int',
                'default' => '3',
                'settings' => [
                    'minValue' => 1,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'maxLen',
                'group' => 'Review_in_column',
                'title' => 'review.maxLen',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'section_id',
                'group' => 'Review_in_column',
                'title' => 'review.section_id_editor',
                'editor' => 'string',
            ],
            [
                'section' => 'all',
                'name' => 'typeShow',
                'group' => 'Review_in_column',
                'title' => 'review.field_template',
                'editor' => 'select',
                'options' => Api::getTypeShowReviews('column'),
                'default' => 'list',
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
