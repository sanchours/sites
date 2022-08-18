<?php

namespace skewer\components\import\field;

use skewer\base\log\Logger;
use skewer\components\gallery\Album;
use skewer\components\gallery\Photo;
use yii\base\ErrorException;
use yii\helpers\FileHelper;

/**
 * Обработчик поля типа галерея.
 */
class GalleryLink extends Gallery
{
    protected $timeout = 5;

    protected static $parameters = [
        'delimiter' => [
            'title' => 'field_gallery_delimiter',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => ',',
        ],
        'recreate' => [
            'title' => 'field_gallery_recreate',
            'datatype' => 's',
            'viewtype' => 'check',
            'default' => '0',
        ],
    ];

    const ImageDir = 'galleryLink/';

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @throws \Exception
     * @throws \yii\base\Exception
     *
     * @return bool|int|mixed
     */
    public function getValue()
    {
        $this->album = $this->getGoodsRow()->getData()[$this->fieldName];

        //если не нужна перезапись и фотки уже есть
        if (!$this->recreate && Photo::getCountByAlbum($this->album)) {
            return $this->album;
        }

        $sVal = implode($this->delimiter, $this->values);
        $sDirPath = IMPORT_FILEPATH . self::ImageDir;

        if (!is_dir($sDirPath)) {
            $bRes = FileHelper::createDirectory($sDirPath);
            if (!$bRes) {
                throw new \Exception('no create directory for images or file.');
            }
        }

        if ($sVal) {
            //создадим альбом
            if (!$this->album) {
                $this->album = Album::create4Catalog();
            }

            //проверить на наличие фотки на сервере, если нет
            //получаем список фото
            $aValues = explode($this->delimiter, $sVal);
            foreach ($aValues as $key => $sValue) {
                //имя файла с расширением
                $sPhotoName = urldecode(basename($sValue));
                if (!file_exists($sDirPath . $sPhotoName)) {
                    //тогда грузим
                    $res = $this->loadFile($sValue, $sPhotoName);
                    //если загрузка была успешна тогда добавляем этот файл для обработки
                    if ($res) {
                        $this->photos[] = $sDirPath . $sPhotoName;
                    }
                } else {
                    $this->photos[] = $sDirPath . $sPhotoName;
                }
            }
        }

        return $this->album;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        parent::afterSave();

        //Удаляем фотки из папки import ппосле импорта
        if ($this->photos) {
            foreach ($this->photos as $sPhoto) {
                if (file_exists($sPhoto)) {
                    unlink($sPhoto);
                }
            }
        }
    }

    private function loadFile($sFile, $sName)
    {
        $sFile = trim($sFile);

        if (!$sFile) {
            return 0;
        }

        $load = 0;
        $curl = curl_init($sFile);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($info['http_code'] != '200') {
            Logger::dump('Возникла ошибка при запросе картинки.');
            Logger::dump('URL: ' . $info['url']);
            Logger::dump('Http код: ' . $info['http_code']);
            Logger::dump('Content Type: ' . $info['content_type']);
            Logger::dump('Error: ' . $error);
            $this->logger->setListParam(
                'error_list',
                'Фото не было загружено! ' . $sFile . ' Причина: http-code ' . $info['http_code'] . ', error: ' . $error
            );
        } else {
            $sDirPath = IMPORT_FILEPATH . self::ImageDir;
            if ($content) {
                try {
                    if (file_exists($sDirPath . $sName)) :
                        unlink($sDirPath . $sName);
                    endif;
                    $fp = fopen($sDirPath . $sName, 'x');
                    fwrite($fp, $content);
                    fclose($fp);
                    $load = 1;
                } catch (ErrorException $e) {
                    Logger::dumpException($e);
                    $this->logger->setListParam(
                        'error_list',
                        'Фото не было загружено! Возникла ошибка при записи файла. ' . $sFile
                    );
                    $load = 0;

                    return $load;
                }
            } else {
                $this->logger->setListParam(
                    'error_list',
                    'Возникла ошибка при получении фото.'
                );
                $load = 0;
            }
        }

        return $load;
    }
}
