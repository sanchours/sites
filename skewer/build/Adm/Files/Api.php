<?php

namespace skewer\build\Adm\Files;

use skewer\helpers;

/**
 * Api для работы с браузером загруженных фалов
 * Class Api.
 */
class Api
{
    /** @var string имя директории миниатюр */
    public static $thumbDirName = '_thumb';

    /**
     * Запрос набора файлов раздела (без серверного пути).
     *
     * @param int $iSectionId id раздела
     *
     * @return array
     */
    public static function getFiles($iSectionId)
    {
        if (!$iSectionId) {
            return [];
        }

        // запросить файлы
        $aFiles = helpers\Files::getFilesFromSection($iSectionId, '', false, [], 'modifyDate', 'DESC');

        if (!$aFiles) {
            return [];
        }

        // удрать поле серверного имени
        foreach ($aFiles as $iKey => $aFile) {
            unset($aFiles[$iKey]['serverPath']);
        }

        return $aFiles;
    }

    /**
     * Удаляет файл.
     *
     * @static
     *
     * @param int $iSectionId id раздела
     * @param string $sFileName имя файла
     *
     * @return bool
     */
    public static function deleteFile($iSectionId, $sFileName)
    {
        // взять раздел
        $sDir = helpers\Files::checkFilePath($iSectionId);

        // раздел есть и файл удален
        $bRes = (bool) ($sDir and helpers\Files::remove($sDir . $sFileName));

        if ($bRes) {
            self::deleteThumbnail($sDir . $sFileName);
        }

        return $bRes;
    }

    /**
     * Загружает переданный набор файлов в прикрепленнцю к разделу директорию.
     *
     * @static
     *
     * @param int $iSectionId id раздела
     *
     * @throws \Exception
     *
     * @return array ( total, loaded )
     */
    public static function uploadFiles($iSectionId)
    {
        $iTotal = 0;
        $iLoaded = 0;
        $aFiles = [];

        // инициализация загрузчика
        $aFilter['allowExtensions'] = \Yii::$app->getParam(['upload', 'allow', 'files']);
        $aFilter['size'] = \Yii::$app->getParam(['upload', 'maxsize']);

        $aFilter['imgMaxWidth'] = \Yii::$app->getParam(['upload', 'images', 'maxWidth']);
        $aFilter['imgMaxHeight'] = \Yii::$app->getParam(['upload', 'images', 'maxHeight']);

        $aValues = static::getLanguage4Uploader();
        helpers\UploadedFiles::loadErrorMessages($aValues);

        $oUplFiles = helpers\UploadedFiles::get($aFilter, FILEPATH, PRIVATE_FILEPATH);
        $oUplFiles->setOnUpload(['skewer\\build\\Adm\\Files\\Api', 'onFileUpload']);

        $aErrorMessages = [];

        if ($oUplFiles->count()) {
            /* @noinspection PhpUnusedLocalVariableInspection */
            foreach ($oUplFiles as $file) {
                try {
                    ++$iTotal;
                    if ($sError = $oUplFiles->getError()) {
                        throw new \Exception($sError);
                    }
                    if ($sFilePath = $oUplFiles->UploadToSection($iSectionId)) {
                        ++$iLoaded;
                        $aFiles[] = basename($sFilePath);
                    }
                } catch (\Exception $e) {
                    $aErrorMessages[] = $e->getMessage();
                }
            }
        }

        return [
            'files' => $aFiles,
            'total' => $iTotal,
            'loaded' => $iLoaded,
            'errors' => $aErrorMessages,
        ];
    }

    /**
     * Обработчик после загрузки файла.
     *
     * @static
     *
     * @param $aItem
     *
     * @return bool
     */
    public static function onFileUpload($aItem)
    {
        // инициализация переменных
        $sBaseDir = $aItem['fileDir'];
        $sName = $aItem['fileName'];
        $sThumbDir = $sBaseDir . self::$thumbDirName . '/';
        $sSrcFileName = $aItem['name'];
        $sThumbFileName = $sThumbDir . $sName;
        $iWidth = 85;
        $iHeight = 64;
        // проверка на картинку: нет - выйти
        $aImgTypes = \Yii::$app->getParam(['upload', 'allow', 'images']);
        if (!$aImgTypes) {
            return false;
        }
        if (!preg_match('/(' . implode('|', $aImgTypes) . ')$/', $sName)) {
            return false;
        }

        // проверка наличий директории
        if (!is_dir($sThumbDir)) {
            mkdir($sThumbDir, 0775);
        }

        $aValues = self::getLanguage4Image();
        helpers\Image::loadErrorMessages($aValues);
        $oImage = new helpers\Image();

        if (!$oImage->load($sSrcFileName)) {
            return false;
        }

        $oImage->resize($iWidth, $iHeight);

        $sThumbFileName = $oImage->save($sThumbFileName);

        return $sThumbFileName;
    }

    /**
     * Возвращает имя миниатюры для файла
     * (самой миниатюры может и не быть).
     *
     * @static
     *
     * @param $sFileName
     *
     * @return mixed
     */
    public static function getThumbName($sFileName)
    {
        return preg_replace(
            sprintf('/\%s([^\%1$s]+)$/', \DIRECTORY_SEPARATOR),
            sprintf('/%s/$1', self::$thumbDirName),
            $sFileName
        );
    }

    /**
     * Удаление миниатюры по имени фалйа.
     *
     * @static
     *
     * @param $sFileName
     */
    public static function deleteThumbnail($sFileName)
    {
        $sThumbName = self::getThumbName($sFileName);

        if (file_exists($sThumbName)) {
            unlink($sThumbName);
        }
    }

    /**
     * Отдает набор текстов ошибок для загрузчика файлов.
     *
     * @return array
     */
    public static function getLanguage4Uploader()
    {
        return [
            'error_upload' => \Yii::t('files', 'error_upload'),
            'error_upload_not_post' => \Yii::t('files', 'error_upload_not_post'),
            'error_max_size' => \Yii::t('files', 'error_max_size'),
            'error_not_required' => \Yii::t('files', 'error_not_required'),
            'error_no_image' => \Yii::t('files', 'error_no_image'),
            'error_invalid_size' => \Yii::t('files', 'error_invalid_size'),
            'error_invalid_format' => \Yii::t('files', 'error_invalid_format'),
            'error_invalid_file_type' => \Yii::t('files', 'error_invalid_file_type'),
            'error_invalid_file_format' => \Yii::t('files', 'error_invalid_file_format'),
        ];
    }

    /**
     * Отдает набор текстов ошибок для загрузчика файлов.
     *
     * @return array
     */
    public static function getLanguage4Image()
    {
        return [
            'error_not_found' => \Yii::t('files', 'error_image_not_found'),
            'error_max_size' => \Yii::t('files', 'error_image_max_size'),
            'error_invalid_format' => \Yii::t('files', 'error_image_invalid_format'),
        ];
    }
}
