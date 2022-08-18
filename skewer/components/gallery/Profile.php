<?php

namespace skewer\components\gallery;

use skewer\base\SysVar;
use skewer\components\gallery\models\Formats;
use skewer\components\gallery\models\Profiles;
use skewer\helpers\Transliterate;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Апи для работы с профилями галереи.
 * Class Profile.
 */
class Profile
{
    const TYPE_SECTION = 'section'; //6
    const TYPE_CATALOG = 'catalog'; //7
    const TYPE_CATALOG_ADD = 'catalog_add';
    const TYPE_CATALOG4COLLECTION = 'collection'; //8
    const TYPE_NEWS = 'news';
    const TYPE_OPENGRAPH = 'openGraph';
    const TYPE_REVIEWS = 'reviews';
    const TYPE_CATEGORYVIEWER = 'category';
    const TYPE_FAVICON = 'favicon';
    const TYPE_ARTICLES = 'articles';
    const TYPE_MOBILE = 'images_for_mobile';
    const TYPE_DICT = 'images_for_dict';

    const MAX_ALIAS_SIZE = 50; // Максимальная длина alias профиля (увеличивать вместе с типом поля в БД)

    /**
     * Возвращает список доступных типов профилей.
     *
     * @return array|false
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SECTION => \Yii::t('gallery', 'profile_type_section'),
            self::TYPE_CATALOG => \Yii::t('gallery', 'profile_type_catalog'),
            self::TYPE_CATALOG_ADD => Yii::t('gallery', 'profile_type_catalog_add'),
            self::TYPE_CATALOG4COLLECTION => \Yii::t('gallery', 'profile_type_cat4col'),
            self::TYPE_NEWS => \Yii::t('gallery', 'profile_type_news'),
            self::TYPE_OPENGRAPH => \Yii::t('gallery', 'profile_type_openGraph'),
            self::TYPE_REVIEWS => \Yii::t('gallery', 'profile_type_reviews'),
            self::TYPE_CATEGORYVIEWER => \Yii::t('gallery', 'profile_type_category'),
            self::TYPE_FAVICON => \Yii::t('gallery', 'profile_type_favicon'),
            self::TYPE_ARTICLES => \Yii::t('gallery', 'profile_type_articles'),
            self::TYPE_MOBILE => \Yii::t('gallery', 'profile_type_mobile'),
            self::TYPE_DICT => \Yii::t('gallery', 'profile_type_dict'),
        ];
    }

    /**
     * Возвращает профайл по id.
     *
     * @param int $iProfileId Id профиля
     *
     * @return array|bool Возвращает данные профиля или false
     */
    public static function getById($iProfileId)
    {
        if ((!$iProfileId = (int) $iProfileId) or
             (!$aProfile = models\Profiles::find()
                 ->where(['id' => $iProfileId])
                 ->asArray()->one())) {
            return false;
        }

        return $aProfile;
    }

    /**
     * Ограничения на загружаемые файлы, соответствующие указанному профилю.
     *
     * @param $iProfileId
     *
     * @return array
     *
     * @internal param $sProfileType - тип профиля
     */
    public static function getUploadLimiting($iProfileId)
    {
        if (!$iProfileId || !$oProfile = Profiles::findOne($iProfileId)) {
            return [];
        }

        $aFilter = [];

        switch ($oProfile->type) {
            case self::TYPE_OPENGRAPH:

                if (SysVar::get('OpenGraph.onCheckSizeImage')) {
                    if ($oFormat = Formats::findOne(['profile_id' => $oProfile->id, 'name' => 'format_openGraph'])) {
                        if ($oFormat->width) {
                            $aFilter['imgMinWidth'] = $oFormat->width;
                        }

                        if ($oFormat->height) {
                            $aFilter['imgMinHeight'] = $oFormat->height;
                        }
                    }
                }

                break;
        }

        return $aFilter;
    }

    /**
     * Возвращает профайл по alias.
     *
     * @param int $sAlias alias профиля
     *
     * @return array|bool Возвращает данные профиля или false
     */
    public static function getByAlias($sAlias)
    {
        if ((!$sAlias) or
             (!$aProfile = models\Profiles::find()
                 ->where(['alias' => $sAlias])
                 ->asArray()->one())) {
            return false;
        }

        return $aProfile;
    }

    /**
     * Возвращает использумый по умолчанию профайл указанного типа или массив профайлов по типу
     * Если нет профилей используемых по умолчанию, то возвращается первый профиль указанного типа.
     *
     * @param string $sProfileType Тип профиля
     * @param bool $bAll Получить все профиля заданного типа?
     *
     * @return array Возвращает профиль/массив профилей в формате id => title
     */
    public static function getActiveByType($sProfileType, $bAll = false)
    {
        if (!$sProfileType) {
            return [];
        }
        $oProfile = models\Profiles::find()
            ->where(['type' => $sProfileType, 'active' => 1])
            ->asArray();
        if ($bAll) {
            $aProfile = $oProfile->all();
        } else {
            $aProfile = $oProfile->orderBy(['default' => SORT_DESC])->limit(1)->all();
        }
        if ($aProfile) {
            return ArrayHelper::map($aProfile, 'id', 'title');
        }

        return [];
    }

    /**
     * Получить id профиля по умолчанию.
     *
     * @param string $type Тип профиля
     * @param bool $bThrowException - выбрасывать исключение при отсутствии профиля?
     *
     * @throws UserException Сообщение об отсутствии профиля
     *
     * @return int| bool  - id профиля | false  - в случае отсутствия
     */
    public static function getDefaultId($type, $bThrowException = true)
    {
        if ($aProfile = self::getActiveByType($type)) {
            return key($aProfile);
        }

        if ($bThrowException) {
            // profile_error_def_catalog, profile_error_def_section, profile_error_def_collection
            throw new UserException(\Yii::t('gallery', "profile_error_def_{$type}"));
        }

        return false;
    }

    /**
     * Получить список всех профилей.
     *
     * @param bool $bOrderByType Сортировать по типу?
     * @param bool $bActive Получить только активные?
     * @param bool $bOnlyTitle Только названия?
     *
     * @return array
     */
    public static function getAll($bOrderByType = true, $bActive = false, $bOnlyTitle = false)
    {
        $oARQuery = models\Profiles::find();

        if ($bActive) {
            $oARQuery->where(['active' => 1]);
        }
        if ($bOrderByType) {
            $oARQuery->orderBy('type');
        }
        if ($bOnlyTitle) {
            $oARQuery->select('id, title');
        }

        $aProfiles = $oARQuery->asArray()->indexBy('id')->all() ?: [];

        return ($bOnlyTitle) ? ArrayHelper::getColumn($aProfiles, 'title') : $aProfiles;
    }

    /**
     * Получить новый профиль.
     *
     * @param array $aData Данные для заполнения нового профиля
     *
     * @return array
     */
    public static function getProfileBlankValues(array $aData = [])
    {
        $oProfile = new models\Profiles();
        if ($aData) {
            $oProfile->setAttributes($aData);
        }

        return $oProfile->getAttributes();
    }

    /**
     * Генерирует/проверяет/исправляет псевдоним для профиля с учётом того, что оно должно быть уникальным
     *
     * @param string $sAlias Текущий псевдоним
     * @param string $sTitle Название профиля
     * @param int $iOldProfileId Id текущего профиля
     *
     * @return string Возвращает корректный псевдоним либо пустую строку
     */
    private static function generateAlias($sAlias, $sTitle, $iOldProfileId)
    {
        $iOldProfileId = (int) $iOldProfileId;

        if (!$sAlias = preg_replace('/[^a-z0-9-_]/Uis', '', $sAlias)) { // Если не задано тех.имя или задано не корректно, то сгенерировать
            $sAlias = Transliterate::generateAlias($sTitle) ?: 'newname';
        }

        $sNewAlias = $sAlias = mb_substr($sAlias, 0, self::MAX_ALIAS_SIZE); // Ограничить размер

        for ($i = 1; ($oHasProfile = models\Profiles::find()->where("id != {$iOldProfileId}")->andWhere(['alias' => $sNewAlias])->one()) and ($i < 100); ++$i) { // Добиться уникального тех.имени формата внутри одного профиля с защитой от бесконечного цикла
            $sNewAlias = mb_substr($sAlias, 0, self::MAX_ALIAS_SIZE - mb_strlen($i) - 1) . "-{$i}";
        }

        return $oHasProfile ? '' : $sNewAlias; // Если уникальности псевдонима не удалось достигнуть, то вызвать ошибку заполнения поля
    }

    /**
     * Добавляет либо обновляет данные профиля.
     *
     * @param array $aData Данные провиля
     * @param int $iProfileId id профиля
     *
     * @throws \Exception|UserException Сообщение об ошибки валидации полей
     *
     * @return bool|int id созданной записи или \Exception / false
     */
    public static function setProfile(array $aData, $iProfileId = 0)
    {
        if ($iProfileId) { // Изменение профиля с валидацией полей
            if (!$oProfile = models\Profiles::findOne($iProfileId)) {
                throw new \Exception(\Yii::t('gallery', 'general_field_empty'));
            }
        } else { // Вставка нового профиля
            $oProfile = new models\Profiles();
        }

        $oProfile->setAttributes($aData);
        $oProfile->alias = self::generateAlias($oProfile->alias, $oProfile->title, $iProfileId);
        if ($oProfile->save(true)) {
            return $oProfile->id;
        }

        if ($oProfile->hasErrors()) { // Если возникла ошибка валидации, то выбросить исключение
            $sFirstError = \yii\helpers\ArrayHelper::getColumn($oProfile->errors, '0', false)[0];
            throw new UserException($sFirstError);
        }

        return false;
    }

    /**
     * Устанавливает профиль, используемый по умолчанию для каждого типа
     * По умолчанию может использоваться только один профиль внутрни одного типа профилей.
     *
     * @param int $iProfileId id профиля
     */
    public static function setDefaultProfile($iProfileId)
    {
        /** @var models\Profiles $oProfile */
        if ($iProfileId and
            $oProfile = models\Profiles::findOne($iProfileId)) {
            models\Profiles::updateAll(['default' => 0], ['type' => $oProfile->type]);
            models\Profiles::updateAll(['default' => 1], ['id' => $oProfile->id]);
        }
    }

    /**
     * Не использовать профиль по умолчанию.
     *
     * @param int $iProfileId id профиля
     */
    public static function unsetDefaultProfile($iProfileId)
    {
        models\Profiles::updateAll(['default' => 0], ['id' => $iProfileId]);
    }

    /**
     * Удаляет профиль.
     *
     * @param int $iProfileId id профиля
     *
     * @return bool
     */
    public static function removeProfile($iProfileId)
    {
        if (!$iProfileId = (int) $iProfileId) {
            return false;
        }

        // Удаление связанных форматов
        if ($aFormats = Format::getByProfile($iProfileId)) {
            foreach ($aFormats as $aFormat) {
                Format::removeFormat($aFormat['id']);
            }
        }

        return models\Profiles::deleteAll(['id' => $iProfileId]) >= 0;
    }
}
