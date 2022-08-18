<?php

namespace skewer\build\Page\CatalogViewer;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends \skewer\build\Adm\ParamSettings\Prototype
{
    public static $aGroups = [
        'catalog' => 'catalog.groups_catalog',
        'catalog2' => 'catalog.groups_catalog2',
        'catalog3' => 'catalog.groups_catalog3',
        'collection' => 'catalog.groups_catalog_collection',
    ];

    public static $iGroupSortIndex = 40;

    /** {@inheritdoc} */
    public function getList()
    {
        return [
            /* catalog */
            [
                'name' => 'titleOnMain',
                'group' => 'catalog',
                'title' => 'catalog.param_title',
                'section' => 'main',
            ],
            [
                'name' => 'onPage',
                'group' => 'catalog',
                'title' => 'catalog.param_on_page',
                'section' => 'main',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'template',
                'group' => 'catalog',
                'title' => 'editor.type_catalog_view',
                'section' => 'main',
                'editor' => 'select',
                'options' => self::getTemplatesOnMain(),
                'default' => 'list',
            ],
            /* catalog 2 */
            [
                'name' => 'titleOnMain',
                'group' => 'catalog2',
                'title' => 'catalog.param_title',
                'section' => 'main',
            ],
            [
                'name' => 'onPage',
                'group' => 'catalog2',
                'title' => 'catalog.param_on_page',
                'section' => 'main',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'template',
                'group' => 'catalog2',
                'title' => 'editor.type_catalog_view',
                'section' => 'main',
                'editor' => 'select',
                'options' => self::getTemplatesOnMain(),
                'default' => 'list',
            ],
            /* catalog 3 */
            [
                'name' => 'titleOnMain',
                'group' => 'catalog3',
                'title' => 'catalog.param_title',
                'section' => 'main',
            ],
            [
                'name' => 'onPage',
                'group' => 'catalog3',
                'title' => 'catalog.param_on_page',
                'section' => 'main',
                'editor' => 'int',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'template',
                'group' => 'catalog3',
                'title' => 'editor.type_catalog_view',
                'section' => 'main',
                'editor' => 'select',
                'options' => self::getTemplatesOnMain(),
                'default' => 'list',
            ],
        ];
    }

    /** {@inheritdoc} */
    public function saveData()
    {
    }

    /** Возвращает список доступных каталожных шаблонов для каталога главной страницы */
    private static function getTemplatesOnMain()
    {
        static $aTemplates;
        if (!$aTemplates) {
            $aTemplates = \yii\helpers\ArrayHelper::getColumn(State\ListOnMain::$aTemplates, 'title');
        }

        return $aTemplates ?: [];
    }

    public function getInstallationParam()
    {
        return [];
    }
}
