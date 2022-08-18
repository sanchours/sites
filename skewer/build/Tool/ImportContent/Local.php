<?php

namespace skewer\build\Tool\ImportContent;

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

        $sDir = __DIR__ . \DIRECTORY_SEPARATOR . 'files' . \DIRECTORY_SEPARATOR;

        switch ($sFileName) {
            case Api::IMPORT_BLANK_FILE_NEWS:
                $sFileTitle = \Yii::t('ImportContent', 'import_filename_news');
                break;
            case Api::IMPORT_BLANK_FILE_REVIEWS:
                $sFileTitle = \Yii::t('ImportContent', 'import_filename_reviews');
                break;
            case Api::IMPORT_BLANK_FILE_ARTICLES:
                $sFileTitle = \Yii::t('ImportContent', 'import_filename_articles');
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
