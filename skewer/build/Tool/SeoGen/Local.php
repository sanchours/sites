<?php

namespace skewer\build\Tool\SeoGen;

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

        $sMode = $this->getStr('mode');

        $sDir = '';

        switch ($sMode) {
            case 'export':
                $sDir = PRIVATE_FILEPATH . Api::SEO_DIRECTORY . \DIRECTORY_SEPARATOR;
                $sFileTitle = \Yii::t('SeoGen', 'export_filename');
                break;
            case 'import':
                $sDir = __DIR__ . \DIRECTORY_SEPARATOR . 'files' . \DIRECTORY_SEPARATOR;
                $sFileTitle = \Yii::t('SeoGen', 'import_filename');
                break;
        }

        // путь к файлу
        $sFilePath = $sDir . $sFileName;

        if (!is_file($sFilePath)) {
            throw new UserException('file not found');
        }
        Download::protectedFile($sFilePath, $sFileTitle . '.xlsx');

        // выйти
        exit;
    }
}
