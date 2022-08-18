<?php

namespace skewer\build\Page\Main\templates\categoryViewer\clothing;

use skewer\components\design\TplSwitchCategoryViewer;

class Switcher extends TplSwitchCategoryViewer
{
    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Одежда';
    }

    public function getSettingsFormat()
    {
        return [
            'width' => 0,
            'height' => 360,
            'resize_on_larger_side' => 0,
            'scale_and_crop' => 1,
            'use_watermark' => 0,
            'watermark' => '',
            'watermark_align' => 84,
            'active' => 1,
        ];
    }
}
