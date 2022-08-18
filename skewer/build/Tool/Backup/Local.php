<?php

namespace skewer\build\Tool\Backup;

use skewer\build\Tool;
use skewer\helpers\Download;
use yii\base\UserException;

/**
 * Модуль для скачивания файлов бэкапов (базы и файлов).
 */
class Local extends Tool\LeftList\ModulePrototype
{
    /** имя параметра "имя файла" */
    const keyFile = 'fileName';

    /**
     * Метод - исполнитель функционала.
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
        $sFileName = basename($this->getStr(self::keyFile));

        // путь к файлу
        $sFilePath = ROOTPATH . 'backup/' . $sFileName;

        if (!is_file($sFilePath)) {
            throw new UserException('file not found');
        }
        Download::protectedFile($sFilePath);

        // выйти
        exit;
    }
}
