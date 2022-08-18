<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\orm\Query;

/**
 * API для работы с сущностями
 * Class Entity.
 */
class Entity
{
    /** тип - словарь */
    const TypeDictionary = 0;

    /** тип - базовая карточка */
    const TypeBasic = 1;

    /** тип - расширенная карточка */
    const TypeExtended = 2;

    /**
     * Отдает модель по имени карточки или будет выброшено исключение.
     *
     * @param int|string $card Имя или идентификатор сущности
     *
     * @return ft\Model
     */
    public static function getModel($card)
    {
        return ft\Cache::get($card);
    }

    /**
     * Карточка товара по идентификатору.
     *
     * @param null|int|string $id Идентификатор карточки
     *
     * @return model\EntityRow
     */
    public static function get($id = null)
    {
        if (!$id) {
            return model\EntityTable::getNewRow();
        }

        if (is_numeric($id)) {
            return model\EntityTable::find($id);
        }

        return model\EntityTable::findOne(['name' => $id]);
    }

    /**
     * Сброс кеша для всех сущностей.
     *
     * @return bool
     */
    public static function clearCache()
    {
        ft\Cache::clearCache();

        return model\EntityTable::update()->set('cache', '')->get();
    }

    /**
     * Сборка - создание модели и кеширование.
     *
     * @param int|string $id Идентификатор сущности
     *
     * @return bool
     */
    public static function build($id)
    {
        if (!$id) {
            return false;
        }

        if (!$oCard = self::get($id)) {
            return false;
        }

        $oCard->updCache();

        return true;
    }

    /**
     * Метод получения ActiveRecord записи по имени сущности.
     *
     * @param string $mBaseCard
     * @param array $condition
     *
     * @throws \Exception
     * @throws ft\Exception
     *
     * @return CatalogGoodRow
     */
    public static function getItemRow($mBaseCard, $condition = [])
    {
        $oModel = ft\Cache::get($mBaseCard);

        if (!$oModel) {
            throw new \Exception('Не найдена модель для карточки.');
        }
        $oQuery = Query::SelectFrom($oModel->getTableName(), $oModel);

        if (!$oQuery) {
            throw new \Exception('Не найдена модель для карточки.');
        }
        if ($condition) {
            $oQuery->where($condition);
        }

        return $oQuery->getOne();
    }

    /**
     * Получить приставку имени таблицы для нужного типа каталожной сущности.
     *
     * @param int $iType Тип каталожной сущности
     *
     * @return string
     */
    public static function getTablePreffix($iType)
    {
        return \Yii\helpers\ArrayHelper::getValue(['cd_', 'co_', 'ce_'], $iType, 'co_');
    }
}
