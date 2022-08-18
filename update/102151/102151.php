<?php

use skewer\components\config\PatchPrototype;

/**
 * Class Patch102151
 */
class Patch102151 extends PatchPrototype
{
    public $sDescription = 'Добавляем лицензионное соглашение';

    public $bUpdateCache = false;

    public function execute()
    {
        $this->copy('LICENSE.html', 'LICENSE.html');
    }

    /**
     * Копируем файл
     * @param string $sFilePath имя файла в текущей директории
     * @param string $sDistPath путь до места назначения от коння сайта
     */
    private function copy($sFilePath, $sDistPath)
    {
        $sFilePath = __DIR__ . DIRECTORY_SEPARATOR . $sFilePath;
        $sDistPath = ROOTPATH . $sDistPath;
        if (file_exists($sDistPath)) {
            unlink($sDistPath);
        }
        copy($sFilePath, $sDistPath);
    }
}
