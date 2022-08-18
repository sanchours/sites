<?php

namespace skewer\components\gallery;

use skewer\base\ui;
use skewer\helpers\Transliterate;
use yii\base\UserException;

/**
 * Api для работы с форматами.
 * Class Format.
 */
class Format
{
    const MAX_NAME_SIZE = 100; // Максимальная длина тех.имени формата (увеличивать вместе с типом поля в БД)

    /**
     * Возвращает формат по id.
     *
     * @param int $iFormatId Id формата
     *
     * @return bool|models\Formats Возвращает формат или false
     */
    public static function getById($iFormatId)
    {
        if (!$iFormatId = (int) $iFormatId) {
            return false;
        }
        /** @var models\Formats $oFormat */
        if ($oFormat = models\Formats::findOne($iFormatId)) {
            return $oFormat;
        }

        return false;
    }

    /**
     * Возвращает набор форматов по id профиля, равному $iProfileId.
     *
     * @param int $iProfileId
     * @param bool $bActive Только активные?
     *
     * @return array Возвращает массив элеметов
     */
    public static function getByProfile($iProfileId, $bActive = false)
    {
        if (!$iProfileId = (int) $iProfileId) {
            return [];
        }

        $oARQuery = models\Formats::find()
            ->where(['profile_id' => $iProfileId])
            ->orderBy('priority');
        if ($bActive) {
            $oARQuery->andWhere(['active' => 1]);
        }

        return $oARQuery->asArray()->all();
    }

    /**
     * Возвращает формат галереи по имени.
     *
     * @param string $sFormatName
     * @param $iProfileId int Id профиля
     *
     * @return bool|models\Formats[] Возвращает массив найденных элеметов либо false
     */
    public static function getByName($sFormatName, $iProfileId)
    {
        if ((!$sFormatName) or
             (!$aFormat = models\Formats::find()
                 ->where(['name' => $sFormatName])
                 ->andWhere(['profile_id' => $iProfileId])
                 ->one())) {
            return false;
        }

        return [$aFormat->getAttributes()];
    }

    /**
     * Получить новый формат
     *
     * @param array $aData Данные для заполнения нового формата
     *
     * @return array
     */
    public static function getFormatBlankValues(array $aData = [])
    {
        $oFormat = new models\Formats();
        if ($aData) {
            $oFormat->setAttributes($aData);
        }

        return $oFormat->getAttributes();
    }

    /**
     * Генерирует/проверяет/исправляет тех.имя для формата с учётом того, что оно должно быть уникальным внутри одного профиля.
     *
     * @param string $sAlias Текущее тех.имя
     * @param string $sTitle Название формата
     * @param int $iProfileId Id профайла
     * @param int $iOldFormatId Id текущего формата
     *
     * @return string Возвращает корректное тех.имя либо пустую строку
     */
    private static function generateAlias($sAlias, $sTitle, $iProfileId, $iOldFormatId)
    {
        $iOldFormatId = (int) $iOldFormatId;

        if (!$sAlias = preg_replace('/[^a-z0-9-_]/Uis', '', $sAlias)) { // Если не задано тех.имя или задано не корректно, то сгенерировать
            $sAlias = Transliterate::generateAlias($sTitle) ?: 'newname';
        }

        $sNewAlias = $sAlias = mb_substr($sAlias, 0, self::MAX_NAME_SIZE); // Ограничить размер

        for ($i = 1; ($oHasFormat = models\Formats::find()->where("id != {$iOldFormatId}")->andWhere(['name' => $sNewAlias, 'profile_id' => $iProfileId])->one()) and ($i < 100); ++$i) { // Добиться уникального тех.имени формата внутри одного профиля с защитой от бесконечного цикла
            $sNewAlias = mb_substr($sAlias, 0, self::MAX_NAME_SIZE - mb_strlen($i) - 1) . "-{$i}";
        }

        return $oHasFormat ? '' : $sNewAlias; // Если уникальности тех.имени не удалось достигнуть, то вызвать ошибку заполнения поля name
    }

    /**
     * Добавляет либо обновляет данные формата.
     *
     * @param array $aData Данные формата
     * @param int $iFormatId id Формата
     *
     * @throws \Exception|UserException Сообщение об ошибки валидации полей
     *
     * @return bool|\Exception|int id созданной записи или \Exception / false
     */
    public static function setFormat(array $aData, $iFormatId = 0)
    {
        if ($iFormatId) { // Изменение формата с валидацией полей
            if (!$oFormat = models\Formats::findOne($iFormatId)) {
                throw new \Exception(\Yii::t('gallery', 'general_field_empty'));
            }
        } else {// Вставка нового формата
            $oFormat = new models\Formats();
            if (isset($aData['profile_id']) and $aData['profile_id']) {
                $aData['priority'] = models\Formats::find()
                    ->where(['profile_id' => $aData['profile_id']])
                    ->max('priority') + 1;
            }
        }

        $oFormat->setAttributes($aData);
        $oFormat->name = self::generateAlias($oFormat->name, $oFormat->title, $oFormat->profile_id, $iFormatId);
        if ($oFormat->save(true)) {
            return $oFormat->id;
        }

        if ($oFormat->hasErrors()) { // Если возникла ошибка валидации, то выбросить исключение
            $sFirstError = \yii\helpers\ArrayHelper::getColumn($oFormat->errors, '0', false);
            throw new UserException($sFirstError[0]);
        }

        return false;
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
    public static function sortFormats($iItemId, $iTargetId, $sOrderType = 'before')
    {
        return ui\Api::sortObjects($iItemId, $iTargetId, models\Formats::className(), $sOrderType, 'profile_id');
    }

    /**
     * Проверить разрешённые к изменению из под учётки Adm поля.
     *
     * @param array $aData Проверяемые поля
     *
     * @return bool
     */
    public static function editParamsForAdmin(array &$aData)
    {
        $aFields = ['title', 'name', 'width', 'height', 'active'];
        foreach ($aData as $sKey => &$aItem) {
            if (in_array($sKey, $aFields)) {
                $aItem['view'] = 'show';
            }
        }

        return true;
    }

    /**
     * Удаляет формат
     *
     * @param int $iFormatId id Формата
     *
     * @return bool
     */
    public static function removeFormat($iFormatId)
    {
        if (!$iFormatId = (int) $iFormatId) {
            return false;
        }

        return models\Formats::deleteAll(['id' => $iFormatId]) >= 0;
    }

    /**
     * Получение данных по кроппингу для каталожных галерей.
     */
    public static function getCrop4Catalog()
    {
        return self::getCropTypeProfile(Profile::TYPE_CATALOG);
    }

    /**
     * Получение данных по кроппингу.
     *
     * @var string - тип профиля
     *
     * @param mixed $typeProfile
     *
     * @return array $aCrop
     */
    public static function getCropTypeProfile($typeProfile)
    {
        return self::getCropByIdProfile(Profile::getDefaultId($typeProfile));
    }

    public static function getCropByIdProfile($idProf)
    {
        $aFormats = Format::getByProfile($idProf, true);
        unset($aFormats['thumbnail']);

        $aCrop = [];
        if (count($aFormats)) {
            foreach ($aFormats as $aFormat) {
                $aCrop[$aFormat['name']] = ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0];
            }
        }

        return $aCrop;
    }
}
