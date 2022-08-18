<?php

namespace skewer\build\Tool\Redirect301;

use skewer\build\Tool;
use skewer\helpers\Download;
use yii\base\UserException;

/**
 * Модуль для скачивания файлов экспорта импорта.
 */
class Local extends Tool\LeftList\ModulePrototype
{
    /**
     * Метод - исполнитель функционала
     * @param string $defAction
     */
    public function execute($defAction = '')
    {
        try {
            // попробовать загрузить файл
            $this->downloadFile();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Отдает файл.
     *
     * @throws UserException
     */
    private function downloadFile()
    {
        // имя файла
        $sFileName = basename($this->getStr('fileName'));

        // путь к файлу
        $sFilePath = WEBPATH . $sFileName;

        if (!is_file($sFilePath)) {
            throw new UserException('file not found');
        }
        Download::protectedFile($sFilePath);

        // выйти
        exit;
    }
}
