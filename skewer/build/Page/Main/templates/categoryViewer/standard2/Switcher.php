<?php

namespace skewer\build\Page\Main\templates\categoryViewer\standard2;

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
        return 'Стандартный(заголовок на изображении)';
    }
}
