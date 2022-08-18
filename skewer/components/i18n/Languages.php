<?php

namespace skewer\components\i18n;

use yii\helpers\ArrayHelper;

/**
 * Класс для работы с языками
 * Class Languages.
 */
class Languages
{
    /**
     * Возвращает список используемых в БД языков.
     *
     * @return array
     */
    public static function getLanguages()
    {
        return ArrayHelper::map(
            models\Language::find()
                ->asArray()
                ->select(['name'])
                ->all(),
            'name',
            'name'
        );
    }

    /**
     * Количество неактивных языков.
     *
     * @return int|string
     */
    public static function getCountNotActiveLanguage()
    {
        return models\Language::find()
            ->where(['not', ['active' => 1]])
            ->count();
    }

    /**
     * Запись по имени языка.
     *
     * @param $sLang
     *
     * @return null|models\Language
     */
    public static function getByName($sLang)
    {
        return models\Language::findOne(['name' => $sLang]);
    }

    /**
     * Полный список языков.
     *
     * @return array
     */
    public static function getAll()
    {
        return models\Language::find()
            ->asArray()
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }

    /**
     * Все активные (Только имена).
     *
     * @return array
     */
    public static function getAllActiveNames()
    {
        return ArrayHelper::map(
            models\Language::find()
                ->select('name')
                ->asArray()
                ->orderBy(['id' => SORT_ASC])
                ->where(['active' => 1])
                ->all(),
            'name',
            'name'
        );
    }

    /**
     * Все активные.
     *
     * @return array
     */
    public static function getAllActive()
    {
        return models\Language::find()
            ->asArray()
            ->orderBy(['id' => SORT_ASC])
            ->where(['active' => 1])
            ->all();
    }

    /**
     * Все неактивные.
     *
     * @return array
     */
    public static function getAllNotActive()
    {
        return models\Language::find()
            ->asArray()
            ->orderBy(['id' => SORT_ASC])
            ->where(['active' => 0])
            ->all();
    }

    /**
     * Все активные в админке.
     *
     * @return array ['name', 'title']
     */
    public static function getAdminActive()
    {
        return models\Language::find()
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->where(['admin' => 1])
            ->select(['name', 'title'])
            ->all();
    }

    /**
     * Установка активности языку.
     *
     * @param $sLanguage
     * @param $bActive
     */
    public static function setActive($sLanguage, $bActive)
    {
        models\Language::updateAll(['active' => $bActive], ['name' => $sLanguage]);
    }
}
