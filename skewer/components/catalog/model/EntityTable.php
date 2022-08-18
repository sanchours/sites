<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm;
use skewer\base\orm\state\StateSelect;

/**
 * Class EntityTable.
 *
 * @method static bool|orm\ActiveRecord|StateSelect|EntityRow find($id = null)
 */
class EntityTable extends orm\TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'c_entity';

    protected static function initModel()
    {
        ft\Entity::get('c_entity')
            ->clear()
            ->setNamespace(__NAMESPACE__)
            ->addField('name', 'varchar(64)', 'Системное имя')
            ->addField('title', 'varchar(255)', 'Название')
            ->addField('type', 'int', 'Тип наследования')
            ->addField('in_base', 'int(1)', 'Сохранить в базе')
            ->addField('desc', 'text', 'Описание')
            ->addField('cache', 'text', 'Кэш')
            ->addField('hide_detail', 'int(1)', 'Скрывать детальную')
            ->addField('priority_sort', 'int(1)', 'Сортировка элементов')
            ->setEditor('hide')
            ->addField('parent', 'int', 'Родительская сущность')
            ->addField('module', 'varchar(64)', 'Модуль')
            ->selectFields('name,title,module')
            ->addValidator('set')
            ->selectField('name')
            ->addIndex('unique')
            ->addColumnSet('editor', 'name,title')
            ->addDefaultProcessorSet()

            ->save();
    }

    public static function getNewRow($aData = [])
    {
        return new EntityRow($aData);
    }

    /**
     * Отает модель сущности по id.
     *
     * @param int $iId Ид сущности
     *
     * @return ft\Model
     * не используется
     */
    public static function getModelById($iId)
    {
        /** @var EntityRow $oEntity */
        $oEntity = self::find($iId);

        if (!empty($oEntity)) {
            return;
        }

        return ft\Cache::get($oEntity->name);
    }

    /**
     * Отдает запись сущности по имени.
     *
     * @param string $sModelName Имя модели
     *
     * @return EntityRow
     */
    public static function getByName($sModelName)
    {
        return self::findOne(['name' => $sModelName]);
    }

    /**
     * Удаление записей товаров из базовой таблицы по расширенной карточке.
     *
     * @param string $base имя базовой карточки
     * @param string $card имя расширенной карточки
     *
     * @throws \Exception
     */
    public static function removeRowBaseByCard($base, $card)
    {
        $sQuery = "DELETE `co_{$base}`
                        FROM `co_{$base}`
                        INNER JOIN `c_goods`
                            ON `co_{$base}`.`id` = `c_goods`.`base_id`
                            AND `c_goods`.`ext_card_name`=:card";

        orm\Query::SQL($sQuery, ['card' => $card]);
    }

    /**
     * Набор имен системных полей.
     *
     * @return array
     */
    public static function getSystemFieldNames()
    {
        return ['alias'];
    }
}
