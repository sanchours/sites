<?php

use skewer\components\config\PatchPrototype;

class Patch85032 extends PatchPrototype
{

    public $sDescription = 'Изменение файлов для init commit';

    public $bUpdateCache = false;

    public function execute()
    {
        $this->copy('404.tpl', 'web/404.php');
        $this->copy('index.tpl', 'web/index.php');
        $this->copy('htaccess.tpl', 'web/.htaccess');
    }

    /**
     * Копируем файл
     * @param string $tplName имя файла в текущей директории
     * @param string $dstPath путь до места назначения от коння сайта
     * @throws \skewer\components\config\UpdateException
     */
    private function copy($tplName, $dstPath) {
        $this->copyFile(
            __DIR__.DIRECTORY_SEPARATOR.$tplName,
            ROOTPATH.$dstPath
        );
    }

}