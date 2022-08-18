<?php

namespace skewer\components\gallery;

use skewer\base\ui;
use skewer\build\Adm\Gallery\Api;
use skewer\build\Adm\Gallery\Search;
use skewer\components\gallery\models\Photos;
use yii\base\ModelEvent;
use yii\base\UserException;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Апи для работы с альбомами.
 * Class Album.
 */
class Album
{
    /**
     * Возвращает данные альбома $iAlbumId.
     *
     * @param int $iAlbumId Id запрашиваемого альбома
     *
     * @return bool|models\Albums
     */
    public static function getById($iAlbumId)
    {
        /** @var models\Albums $oAlbum */
        if ((!$iAlbumId = (int) $iAlbumId) or
            (!$oAlbum = models\Albums::findOne($iAlbumId))) {
            return false;
        }

        return $oAlbum;
    }

    /**
     * Возвращает данные альбома $sAlbumAlias.
     *
     * @param string $sAlbumAlias Alias запрашиваемого альбома
     * @param int $iSectionId
     *
     * @return array
     */
    public static function getByAlias($sAlbumAlias, $iSectionId = 0)
    {
        if (!$sAlbumAlias) {
            return [];
        }

        return models\Albums::find()
            ->where(['alias' => $sAlbumAlias] + ($iSectionId ? ['section_id' => $iSectionId] : []))
            ->asArray()->one();
    }

    /**
     * Добавляет либо обновляет данные альбома. Если указан $iAlbumId,
     * то происходит обновление записи.
     *
     * @param  array $aData Массив данных
     * @param int $iAlbumId Id обновляемого альбома
     *
     * @throws \Exception|UserException Сообщение об ошибки валидации полей
     *
     * @return bool|\Exception|int id созданной записи или \Exception / false
     */
    public static function setAlbum(array $aData, $iAlbumId = 0)
    {
        if ($iAlbumId) { // Изменение альбома с валидацией полей
            if (!$oAlbum = self::getById($iAlbumId)) {
                throw new \Exception(\Yii::t('gallery', 'general_field_empty'));
            }
        } else {// Вставка нового альбома
            $oAlbum = new models\Albums();
            if (isset($aData['section_id']) and $aData['section_id']) {
                $aData['priority'] = models\Albums::find()
                    ->where(['section_id' => $aData['section_id']])
                    ->max('priority') + 1;
            }
        }

        $oAlbum->setAttributes($aData);
        if ($oAlbum->save(true)) {
            return $oAlbum->id;
        }

        if ($oAlbum->hasErrors()) { // Если возникла ошибка валидации, то выбросить исключение
            $sFirstError = \yii\helpers\ArrayHelper::getColumn($oAlbum->errors, '0', false)[0];
            throw new UserException($sFirstError);
        }

        return false;
    }

    /**
     * Возвращает массив видимых (активных) альбомов из раздела $iSectionId.
     *
     * @param int $iSectionId Id раздела
     * @param bool $bWithoutHidden Без скрытых альбомов?
     * @param int $iOnPage количество на страницу
     * @param int $iPage номер страницы
     * @param int $iCount сюда будет возвращено общее количество
     *
     * @return array Возвращает массив найденных альбомов
     */
    public static function getBySection($iSectionId, $bWithoutHidden = true, $iOnPage = 0, $iPage = 1, &$iCount = 0)
    {
        if (!$iSectionId = (int) $iSectionId) {
            return [];
        }

        $oQuery = models\Albums::find()
            ->where(['section_id' => $iSectionId]);

        if ($iOnPage) {
            $oQuery->limit($iOnPage);
            $oQuery->offset(($iPage - 1) * $iOnPage);
        }

        if ($bWithoutHidden) {
            $oQuery->andWhere(['visible' => 1]);
        }

        $aOut = $oQuery->orderBy('priority DESC')
            ->asArray()->all();

        $iCount = (int) $oQuery->count();

        return $aOut;
    }

    /**
     * Добавляет к альбомам кол-во фоток и превьюшку.
     *
     * @param array $aAlbums
     * @param bool $bActive - Только активные
     *
     * @return array $aAlbums
     */
    public static function setCountsAndPreview(array $aAlbums, $bActive = false)
    {
        if (!$aAlbums) {
            return [];
        }

        /** Получим кол-во фоток в альбомах */
        $oQuery = Photos::find()
            ->select(['album_id', 'COUNT(*) AS cnt'])
            ->groupBy(['album_id'])
            ->asArray()
            ->where(['album_id' => ArrayHelper::map($aAlbums, 'id', 'id')]);

        if ($bActive) {
            $oQuery->andWhere(['visible' => 1]);
        }

        $aCounts = ArrayHelper::map($oQuery->all(), 'album_id', 'cnt');

        foreach ($aAlbums as &$aAlbum) {
            $aAlbum['album_count'] = (isset($aCounts[$aAlbum['id']])) ? $aCounts[$aAlbum['id']] : 0;
        }

        // Выбрать последнюю активную превьюшку
        foreach ($aAlbums as &$aAlb) {
            if (!($aAlb['album_count'])) {
                $aAlb['album_img'] = false;
            } else {
                $thumbnail = models\Photos::find()
                    ->select('thumbnail as album_img, alt_title, title as titleImage, description as descriptionImage, priority as priorityPreview')
                    ->orderBy(['priority' => SORT_DESC])
                    ->where(['album_id' => $aAlb['id'], 'visible' => 1])
                    ->asArray()
                    ->one();
                if ($thumbnail) {
                    $aAlb += $thumbnail;
                }
            }
        }

        return array_values($aAlbums);
    }

    /**
     * Возвращает массив видимых (активных) ЗАПОЛНЕННЫХ альбомов из раздела $iSectionId.
     *
     * @param int $iSectionId Id раздела
     * @param bool $bWithoutHidden Без скрытых альбомов?
     * @param int $iOnPage количество на страницу
     * @param int $iPage номер страницы
     * @param int $iCount сюда будет возвращено общее количество
     *
     * @return array Возвращает массив найденных альбомов
     */
    public static function getOnlyWithImages($iSectionId, $bWithoutHidden = true, $iOnPage = 0, $iPage = 1, &$iCount = 0)
    {
        if (!$iSectionId = (int) $iSectionId) {
            return [];
        }

        $oQuery = models\Albums::find()
            ->distinct('photogallery_albums.id')
            ->join('LEFT JOIN', 'photogallery_photos', 'photogallery_albums.id = photogallery_photos.album_id')
            ->where(['section_id' => $iSectionId]);

        if ($bWithoutHidden) {
            $oQuery->andWhere(['photogallery_albums.visible' => 1]);
            $oQuery->andWhere(['photogallery_photos.visible' => 1]);
        }
        $iCount = (int) $oQuery->count();
        if ($iOnPage) {
            $oQuery->limit($iOnPage);
            $oQuery->offset(($iPage - 1) * $iOnPage);
        }

        $aOut = $oQuery->orderBy('photogallery_albums.priority DESC')
            ->asArray()->all();

        return $aOut;
    }

    /**
     * Возвращает Id профиля настроек по Id альбома.
     *
     * @static
     *
     * @param int $iAlbumId Id альбома
     *
     * @return bool|int
     */
    public static function getProfileId($iAlbumId)
    {
        /** @var models\Albums $oAlbum */
        if ((!$iAlbumId = (int) $iAlbumId) or
            (!$oAlbum = models\Albums::findOne($iAlbumId))) {
            return false;
        }

        return $oAlbum->profile_id;
    }

    /**
     * Возвращает тип профиля по Id альбома.
     *
     * @static
     *
     * @param int $iAlbumId Id альбома
     *
     * @return bool|string
     */
    public static function getProfileType($iAlbumId)
    {
        /** @var models\Profiles $oProfile */
        $oProfile = models\Profiles::find()
            ->from(models\Profiles::tableName() . ' AS profiles')
            ->innerJoin(models\Albums::tableName() . ' AS albums', "albums.id = {$iAlbumId} AND profiles.id = albums.profile_id")
            ->one();

        return $oProfile ? $oProfile->type : false;
    }

    /**
     * Отдает шаблонный набор значений для добавления новой записи.
     *
     * @param array $aData Данные для заполнения нового альбома
     *
     * @return array
     */
    public static function getAlbumBlankValues(array $aData = [])
    {
        $oAlbum = new models\Albums();
        if ($aData) {
            $oAlbum->setAttributes($aData);
        }

        return $oAlbum->getAttributes();
    }

    /**
     * Удаляет альбом $iAlbumId. Возвращает true, если удаление прошло успешно либо false вслучае ошибки.
     * Текст ошибки сохраняется в параметр $mError.
     *
     * @param int $iAlbumId Id удаляемого альбома
     * @param bool|string $mError Содержит текст ошибки либо false
     *
     * @throws UserException
     *
     * @return bool
     */
    public static function removeAlbum($iAlbumId, &$mError = false)
    {
        try {
            /* Выбрать альбом */
            if ((!$iAlbumId = (int) $iAlbumId) or
                 (!$oAlbum = models\Albums::findOne($iAlbumId))) {
                throw new UserException(\Yii::t('gallery', 'error_notfound'));
            }
            self::clearAlbum($iAlbumId);
            $oAlbum->delete();
        } catch (\Exception $e) {
            $mError = $e->getMessage();

            return false;
        }

        return true;
    }

    // func

    /** Очистка альбома с удалением всей папки */
    public static function clearAlbum($iAlbumId)
    {
        if (!trim($iAlbumId, '\/')) {
            return;
        }

        // Удаление изображений
        Photo::removeFromAlbum($iAlbumId);

        // Удаление директории альбома.
        FileHelper::removeDirectory(Api::getAlbumDir($iAlbumId));
    }

    /**
     * Удаляет альбомы и изображения из раздела $iSectionId.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        if ($aAlbums = self::getBySection($event->sender->id, false)) {
            foreach ($aAlbums as $aAlbum) {
                $mError = false;
                self::removeAlbum($aAlbum['id'], $mError);
            }
        }
    }

    /**
     * Обновляет поиск при изменении раздела $iSectionId.
     *
     * @param AfterSaveEvent $event
     */
    public static function updateSection(AfterSaveEvent $event)
    {
        if ($event->sender->group != 'content' || $event->sender->name != 'openAlbum') {
            return;
        }

        $aAlbums = self::getBySection($event->sender->parent, true);

        if (!$aAlbums) {
            return;
        }

        $oSearch = new Search();

        if ((int) $event->sender->value) {
            foreach ($aAlbums as $aAlbum) {
                $oSearch->deleteByObjectId($aAlbum['id']);
            }
        } else {
            foreach ($aAlbums as $aAlbum) {
                $oSearch->updateByObjectId($aAlbum['id']);
            }
        }
    }

    /**
     * Сортирует объекты списка.
     *
     * @param int $iItemId id перемещаемого объекта
     * @param int $iTargetId id объекта, относительно которого идет перемещение
     * @param string $sOrderType направление переноса
     *
     * @return bool
     */
    public static function sortAlbums($iItemId, $iTargetId, $sOrderType = 'before')
    {
        // Здесь обратная сортировка
        $sOrderType = ($sOrderType == 'before') ? 'after' : 'before';

        $bRes = ui\Api::sortObjects($iItemId, $iTargetId, models\Albums::className(), $sOrderType, 'section_id');

        $Obj = self::getById($iItemId);
        if ($Obj) {
            $Obj->last_modified_date = date('Y-m-d H:i:s', time());
            $Obj->save();
        }

        return $bRes;
    }

    /**
     * Возвращает первое активное изображение в альбоме
     * Внимание! Метод в сборке не используется, но пригоден поддержке.
     *
     * @param int $iAlbumId id альбома
     * @param string $sFormatName Тех.имя формата
     *
     * @return bool|string
     */
    public static function getFirstActiveImage($iAlbumId, $sFormatName = 'preview')
    {
        if ($aPhoto = Photo::getFromAlbum($iAlbumId, true, 1)) {
            if (isset($aPhoto[0]['images_data'][$sFormatName]['file'])) {
                return $aPhoto[0]['images_data'][$sFormatName]['file'];
            }
        }

        return false;
    }

    /**
     * Изменение видимости альбома.
     *
     * @param int $iAlbumId id альбома
     *
     * @return bool
     */
    public static function toggleActiveAlbum($iAlbumId)
    {
        $oAlbum = models\Albums::findOne($iAlbumId);
        if (!$oAlbum) {
            return false;
        }

        $oAlbum->visible = (int) !$oAlbum->visible;

        return $oAlbum->save();
    }

    /**
     * копируем каталожную!!!! галерею. Другая галерея не скопируется. PS Новые скопируются, которые находятся в папке web\files\gallery.
     *
     * @param $iAlbumId
     *
     * @return bool|int
     */
    public static function copyAlbum($iAlbumId)
    {
        if (!$iAlbumId = (int) $iAlbumId) {
            return false;
        }

        /** @var models\Albums $oAlbum */
        if (!$oAlbum = models\Albums::findOne($iAlbumId)) {
            return false;
        }

        if (!$sTypeProfile = self::getProfileType($iAlbumId)) {
            return false;
        }

        // если галерея не каталожная, то не копируем
        if (!in_array($sTypeProfile, [Profile::TYPE_CATALOG, Profile::TYPE_CATALOG_ADD])) {
            return false;
        }

        $oNewAlbum = new models\Albums();
        $oNewAlbum->setAttributes($oAlbum->getAttributes());

        if (!$oNewAlbum->save()) {
            return false;
        }

        // записываем в базу
        /** @var models\Photos [] $aImages */
        $aImages = models\Photos::findAll(['album_id' => $iAlbumId]);

        foreach ($aImages as $oImg) {
            $oNewImg = new models\Photos();
            $oNewImg->setAttributes($oImg->getAttributes());

            // хранение старой версии файлов (до 26 версии)
            $oNewImg->images_data = str_replace('/catalog\/' . $iAlbumId . '\/', '/catalog\/' . $oNewAlbum->id . '\/', $oNewImg->images_data);
            $oNewImg->thumbnail = str_replace('/catalog/' . $iAlbumId . '/', '/catalog/' . $oNewAlbum->id . '/', $oNewImg->thumbnail);
            $oNewImg->source = str_replace('/catalog/' . $iAlbumId . '/', '/catalog/' . $oNewAlbum->id . '/', $oNewImg->source);

            // новое расположение (с 26 включительно)
            $oNewImg->images_data = str_replace('/gallery\/' . $iAlbumId . '\/', '/gallery\/' . $oNewAlbum->id . '\/', $oNewImg->images_data);
            $oNewImg->thumbnail = str_replace('/gallery/' . $iAlbumId . '/', '/gallery/' . $oNewAlbum->id . '/', $oNewImg->thumbnail);
            $oNewImg->source = str_replace('/gallery/' . $iAlbumId . '/', '/gallery/' . $oNewAlbum->id . '/', $oNewImg->source);

            $oNewImg->album_id = $oNewAlbum->id;

            $oNewImg->save();
        }

        // копируем, если есть что (созданные до 26 версии)
        if (is_dir(FILEPATH . 'catalog/' . $iAlbumId)) {
            FileHelper::copyDirectory(FILEPATH . 'catalog/' . $iAlbumId, FILEPATH . 'catalog/' . $oNewAlbum->id, ['dirMode' => 0755]);
        }

        // копируем, если есть что (после 26)
        if (is_dir(FILEPATH . 'gallery/' . $iAlbumId)) {
            FileHelper::copyDirectory(FILEPATH . 'gallery/' . $iAlbumId, FILEPATH . 'gallery/' . $oNewAlbum->id, ['dirMode' => 0755]);
        }

        return $oNewAlbum->id;
    }

    /**
     * Создание нового альбома для каталога.
     *
     * @return bool|int
     */
    public static function create4Catalog()
    {
        $aDataProfile = [
            'owner' => 'entity',
            'profile_id' => Profile::getDefaultId(Profile::TYPE_CATALOG), // Профиль форматов
            'section_id' => 0,
        ];

        return self::setAlbum($aDataProfile);
    }

    /**
     * Вернёт размеры, передаваемые в фотораму
     * Если хотя бы один из параметров ширина/высота формата $sFormat альбома $iAlbumId равен нулю
     * То функция вернёт максимальные размеры, определённые по списку изображений $aPhotos.
     *
     * @param $iAlbumId - id альбома
     * @param $sFormat - имя формата
     * @param array $aPhotos - массив изображений
     * @param bool $bUseDefaultProfile - использовать профиль "по умолчанию"?
     *
     * @return array - массив размеров формата array( ширина, высота )
     */
    public static function getDimensions4Fotorama($iAlbumId, $sFormat, $aPhotos = [], $bUseDefaultProfile = false)
    {
        $iWidth = $iHeight = 0;
        $iProfileId = false;

        // Если альбомов несколько, то размеры определяются по макс. из списка фотографий
        if (is_array($iAlbumId)) {
            if (!$bUseDefaultProfile) {
                $iProfileId = self::getProfileId($iAlbumId);
            } else {
                if ($sTypeProfile = self::getProfileType($iAlbumId)) {
                    $iProfileId = Profile::getDefaultId($sTypeProfile, false);
                }
            }

            if ($iProfileId !== false) {
                if ($aFormat = Format::getByName($sFormat, $iProfileId)) {
                    $iWidth = (int) ArrayHelper::getValue($aFormat, '0.width', 0);
                    $iHeight = (int) ArrayHelper::getValue($aFormat, '0.height', 0);
                }
            }
        }

        if (!$iWidth || !$iHeight) {
            if ($aTempWidth = ArrayHelper::getColumn($aPhotos, "images_data.{$sFormat}.width")) {
                $iWidth = max($aTempWidth);
            }

            if ($aTempHeight = ArrayHelper::getColumn($aPhotos, "images_data.{$sFormat}.height")) {
                $iHeight = max($aTempHeight);
            }
        }

        return [(int) $iWidth, (int) $iHeight];
    }

    /**
     * Количество альбомов в разделе.
     *
     * @param int $iSectionId - ид раздела
     * @param bool $bWithoutHidden - считать скрытые альбомы?
     *
     * @return int
     */
    public static function getCountAlbumsBySection($iSectionId, $bWithoutHidden = true)
    {
        $oQuery = models\Albums::find()
            ->where(['section_id' => $iSectionId]);

        if ($bWithoutHidden) {
            $oQuery->andWhere(['visible' => 1]);
        }

        $iCount = (int) $oQuery->count('id');

        return $iCount;
    }

    /**
     * Получить урл альбома.
     *
     * @param int $iSectionId - ид род.раздела
     * @param string $sAlias - alias альбома
     * @param int $iId - ид альбома
     *
     * @return string
     */
    public static function getUrl($iSectionId, $sAlias, $iId)
    {
        $sAlias = ($sAlias) ? "alias={$sAlias}" : "id={$iId}";
        $sStr = "[{$iSectionId}][Gallery?{$sAlias}]";

        return \Yii::$app->router->rewriteURL($sStr);
    }
}
