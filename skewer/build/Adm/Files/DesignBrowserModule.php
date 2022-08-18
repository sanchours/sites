<?php

namespace skewer\build\Adm\Files;

use skewer\components\design\Design;

/**
 * Панель отображения набора файлов для панели выбора файлов дизайнерского режима
 * Class DesignBrowserModule.
 */
class DesignBrowserModule extends BrowserModule
{
    /**
     * Отдает id директории для записи.
     *
     * @return int
     */
    public function sectionId()
    {
        return Design::imageDirName;
    }
}
