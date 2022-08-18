<?php

namespace app\skewer\console;

use skewer\base\log\Logger;
use skewer\components\gallery\models\Albums;
use skewer\components\gallery\models\Formats;
use skewer\components\gallery\models\Photos;
use skewer\components\gallery\models\Profiles;
use skewer\components\gallery\Photo;
use skewer\components\gallery\Profile;
use skewer\helpers\Files;
use yii\helpers\ArrayHelper;

class GalleryController extends Prototype
{
    /**
     * Преобразует изображения всех альбомов профиля в заданный формат
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function actionRecrop()
    {
        $this->br();

        $iProfileId = $this->prompt(
            'Введите id профиля:',
            ['required' => true, 'validator' => function ($input, &$error) {
                if (Profile::getById($input) === false) {
                    $error = sprintf('Профиль [%s] не существует', $input);
                    $this->addLogs($error, true);

                    return false;
                }

                return true;
            }]
        );

        $aAlbums = Albums::find()
            ->where(['profile_id' => $iProfileId])
            ->asArray()
            ->all();

        $aAlbumsId = ArrayHelper::getColumn($aAlbums, 'id', []);

        if (($aPhotos = Photo::getFromAlbum($aAlbumsId, false)) === false) {
            $message = sprintf('Альбомы профиля [%d] не имеют изображений', $iProfileId);
            $this->addLogs($message, true);

            return false;
        }

        $aFormats = Formats::find()
            ->where(['profile_id' => $iProfileId])
            ->asArray()
            ->all();

        $aNameFormats = ArrayHelper::getColumn($aFormats, 'name', []);

        $message = sprintf('Доступные форматы: %s', implode(', ', $aNameFormats));
        $this->stdout($message . "\r\n");
        $this->addLogs($message);

        /**
         * @var callable Проверяет есть ли формат в списке форматов
         *
         * @param string $sFormatName - имя формата
         * @param string $sError     - сообщение об ошибке
         *
         * @return bool
         */
        $funcValidateFormats = function ($sFormatName, &$sError) use ($aNameFormats,$iProfileId) {
            if (!in_array($sFormatName, $aNameFormats)) {
                $sError = sprintf("Ошибка: Формат '%s' не найден в списке форматов профиля %d", $sFormatName, $iProfileId);
                $this->addLogs($sError);

                return false;
            }

            return true;
        };

        $sTargetFormat = $this->prompt(
            'Введите имя целевого формата:',
            ['required' => true, 'validator' => $funcValidateFormats]
        );

        $iTotalPhotos = count($aPhotos);

        $mProfileId = [
            'crop' => [$sTargetFormat => []],
            'iProfileId' => $iProfileId,
        ];

        $this->recropPhotos($aPhotos, $mProfileId, $iTotalPhotos, $sTargetFormat);

        $this->br();
    }

    /**
     * Итеративное создание изображения нового формата.
     *
     * @param Photos[] $aPhotos - изображения
     * @param $mProfileId - настройки целевого формата
     * @param $iTotalPhotos - количество фото в альбоме
     * @param $sTargetFormat - имя целевого формата
     *
     * @throws \Exception
     */
    protected function recropPhotos(array $aPhotos, $mProfileId, $iTotalPhotos, $sTargetFormat)
    {
        Files::init(FILEPATH, PRIVATE_FILEPATH);

        $iLoadedPhotos = 0;

        foreach ($aPhotos as &$oPhoto) {
            $sError = $sSource = '';

            $sSource = ($oPhoto->source && file_exists(WEBPATH . $oPhoto->source))
                ? WEBPATH . $oPhoto->source
                : $this->getMaxImage($oPhoto->getPictures());

            if (!$sSource) {
                $sError = sprintf('Ошибка: Не найдено исходное изображение [id=%d]', $oPhoto->id);
                $this->addLogs($sError, true);
            } else {
                $aLoadPhotos = Photo::processImage($sSource, $mProfileId, $oPhoto->album_id, false, false, $sError);

                if ($aLoadPhotos === false) {
                    $sError = sprintf('Ошибка: при обрабоки изображения: текст ошибки - %s [id=%d] [sourse=%s]', $sError, $oPhoto->id, $sSource);
                    $this->addLogs($sError, true);
                } else {
                    /** @var Photos $oPhoto */
                    $aDataPhoto = $oPhoto->getPictures();

                    // Удаляем старое изображение
                    if (isset($aDataPhoto[$sTargetFormat]['file'])) {
                        $sOldFilePath = WEBPATH . $aDataPhoto[$sTargetFormat]['file'];
                        @unlink($sOldFilePath);
                        $message = sprintf('Удалено старое изображение [id=%d]', $oPhoto->id);
                        $this->addLogs($message);
                    } else {
                        $message = sprintf('Не найдено старое изображение [id=%d]', $oPhoto->id);
                        $this->addLogs($message, true);
                    }

                    $aDataPhoto = ArrayHelper::merge($aDataPhoto, $aLoadPhotos);
                    $oPhoto->images_data = json_encode($aDataPhoto);

                    $resultSave = $oPhoto->save(false);
                    // если при сохранении возникла ошибка
                    if (!$resultSave) {
                        $errorMessage = sprintf('Ошибка: при сохранении изображения [id=%d] [sourse=%s]', $oPhoto->id, $sSource);
                        $this->addLogs($errorMessage, true);
                        continue;
                    }

                    $message = sprintf('Загружено изображение [id=%d]', $oPhoto->id);
                    $this->addLogs($message);

                    ++$iLoadedPhotos;

                    $message = sprintf('Загруженно изображений %d/%d', $iLoadedPhotos, $iTotalPhotos);
                    $this->stdout("\r" . $message);
                    $this->addLogs($message);
                }
            }
        }
    }

    /**
     * Список доступных профилей.
     */
    public function actionListProfiles()
    {
        $this->br();

        $aProfiles = Profiles::find()->all();

        /** @var Profiles $oProfile */
        foreach ($aProfiles as $oProfile) {
            $sName = sprintf("%d - %s [ %s ]\r\n", $oProfile->id, $oProfile->title, $oProfile->alias);
            $this->stdout($sName);
        }

        $this->br();
    }

    /**
     * Вернет полный путь к изображению максимального размера.
     *
     * @param $aFormats - список форматов
     *
     * @return string
     */
    protected function getMaxImage($aFormats)
    {
        $sSource = '';
        $aSizeFormat = [];
        foreach ($aFormats as $sFormatName => $aPhoto) {
            if (($aImageSize = @getimagesize(WEBPATH . $aPhoto['file'])) !== false) {
                $aSizeFormat[$sFormatName] = $aImageSize[0] * $aImageSize[1];
            }
        }

        arsort($aSizeFormat);

        foreach ($aSizeFormat as $sFormat => $Value) {
            $sFilePath = WEBPATH . $aFormats[$sFormat]['file'];
            if (file_exists($sFilePath)) {
                $sSource = $sFilePath;
                break;
            }
        }

        return $sSource;
    }

    /**
     * Добавляем ошибку в массив ошибок.
     *
     * @param string $errorMessage
     * @param bool $console Выводить в консоль
     */
    protected function addLogs($errorMessage = '', $console = false)
    {
        if (!is_bool($console)) {
            $console = false;
        }

        // если ошибка помечена для вывода в консоль
        if ($console) {
            $this->stderr($errorMessage . "\r\n");
        }

        Logger::dump($errorMessage);
    }
}
