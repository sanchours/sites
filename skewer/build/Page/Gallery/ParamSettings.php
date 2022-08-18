<?php

namespace skewer\build\Page\Gallery;

use skewer\base\section\Template;
use skewer\build\Adm\ParamSettings\Prototype;

/**
 * Класс, содержащий редактируемые для текущего модуля параметры, используемые в админском модуле "Настройка параметров"
 * (skewer\build\Adm\ParamSettings)
 * Class ParamSettings.
 */
class ParamSettings extends Prototype
{
    public static $aGroups = [
        'gallery' => 'gallery.groups_gallery',
    ];

    public static $iGroupSortIndex = 100;

    /** {@inheritdoc} */
    public function getList()
    {
        $iSection = Template::getTemplateIdForModule('Gallery');

        if (!$iSection) {
            return [];
        }

        return [
            [
                'name' => 'galleryOnPage',
                'group' => 'gallery',
                'label' => 'content',
                'title' => 'gallery.gallery_on_page',
                'editor' => 'int',
                'section' => $iSection,
                'default' => '10',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
                ],
            ],
            [
                'name' => 'photosLimit',
                'group' => 'gallery',
                'label' => 'content',
                'title' => 'gallery.photos_limit',
                'section' => $iSection,
                'editor' => 'int',
                'default' => '150',
                'settings' => [
                    'minValue' => 0,
                    'allowDecimals' => false,
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
