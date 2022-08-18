<?php

namespace skewer\build\Catalog\Collections;

use skewer\base\ft\Cache;
use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\components\catalog;

/**
 * Интерфейс доступа внешним модулям к модели коллекций
 * Class Api.
 */
class Api
{
    /**
     * Получить id карточки коллекции для раздела.
     *
     * @param int $iSectionId Id раздела
     *
     * @throws \Exception
     *
     * @return bool|int
     */
    public static function getCollectionBySection($iSectionId)
    {
        if (!isset(Tree::getVisibleSections()[$iSectionId])) {
            return false;
        }

        $oParam = Parameters::getByName($iSectionId, 'content', 'collectionField');

        if ($oParam) {
            $sCard = explode(':', $oParam->value)[0];
            if (Cache::get($sCard)) {
                return $sCard;
            }
        }

        return false;
    }

    /**
     * Получить все разделы с коллекциями в формате ключ - id, значение - заголовок.
     *
     * @param bool $bOnlyVisible Только видимые?
     *
     * @throws catalog\Exception
     *
     * @return array Массив id разделов с коллекциями
     */
    public static function getCollectionsSections($bOnlyVisible = true)
    {
        /** Список параметров разделов с работающими коллекциями */
        $aCollectionsParams = Parameters::getList()
            ->name('collectionField')
            ->get();

        $aOut = [];

        /** Список всех коллекций каталога */
        $aCollectionsList = catalog\Collection::getCollectionList();

        // Проверка на видимость
        foreach ($aCollectionsParams as $oParam) {
            if ($bOnlyVisible and !isset(Tree::getVisibleSections()[$oParam->parent])) {
                continue;
            }

            if (isset($aCollectionsList[(int) $oParam->value])) {
                $aOut[$oParam->parent] = $aCollectionsList[(int) $oParam->value];
            }
        }

        return $aOut;
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @param mixed $sCard
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate($sCard)
    {
        $oModel = Cache::get($sCard);

        if (!$oModel) {
            return false;
        }

        return (new \yii\db\Query())->select('MAX(`last_modified_date`) as max')->from('cd_' . $oModel->getName())->one();
    }

    /**
     * Отдает кол-во разделов в которых нужна коллекция.
     *
     * @param $iCollectionId
     *
     * @return int|string
     */
    public static function getCountCollectionSections($iCollectionId)
    {
        return ParamsAr::find()
            ->where([
                'value' => $iCollectionId . ':colllect',
                'name' => 'collectionField',
            ])
            ->count();
    }
}
