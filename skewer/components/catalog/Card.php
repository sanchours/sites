<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\SysVar;
use skewer\components\catalog\model\FieldRow;
use yii\helpers\ArrayHelper;

/**
 * API для работы с карточками, словарями
 * Class Card.
 */
class Card extends Entity
{
    /** префикс для тех.имен в sysvar
     * для признака предмета расчета
     * в платежной системе
     * для карточек товара.
     */
    const PREFIX_PAYMENT_OBJECT_NAME = 'catalog.card.';

    /** дефолтная базовая карточка для каталога */
    const DEF_BASE_CARD = 'base_card';

    /** Имя модуля для карточки */
    const ModuleName = 'Catalog';

    const DEF_GOODS_MODULE = 'Catalog';

    const DEF_DICT_LAYER = 'Tool';

    const DEF_COLLECTION_MODULE = 'Collection';

    /** Системное имя поля для сортировки записей */
    const FIELD_SORT = 'priority';

    private static $cache = [];

    /**
     * @param mixed $key
     *
     * @return string
     */
    private static function getCache($key)
    {
        return (isset(self::$cache[$key])) ? self::$cache[$key] : '';
    }

    /**
     * @param array $cache
     * @param mixed $key
     * @param mixed $value
     */
    private static function setCache($key, $value = '')
    {
        self::$cache[$key] = $value;
    }

    /**
     * Все товарные карточки.
     *
     * @param bool $bWithBase Флаг выборки базовых карточек
     *
     * @return model\EntityRow[]
     */
    public static function getGoodsCards($bWithBase = false)
    {
        $aTypes = [self::TypeExtended];

        if ($bWithBase) {
            $aTypes[] = self::TypeBasic;
        }

        return model\EntityTable::find()
            ->where('type', $aTypes)
            ->getAll();
    }

    /**
     * Список товарных карточек.
     *
     * @param bool $bKeyIsName Флаг использования в качестве ключа название
     *
     * @return array
     */
    public static function getGoodsCardList($bKeyIsName = false)
    {
        $aOut = [];
        foreach (self::getGoodsCards() as $oEntity) {
            $aOut[$bKeyIsName ? $oEntity->name : $oEntity->id] = $oEntity->title;
        }

        return $aOut;
    }

    /**
     * Получить title карточки.
     *
     * @param string $name Имя карточки
     *
     * @return string
     */
    public static function getTitle($name)
    {
        if (is_numeric($name)) {
            $card = model\EntityTable::findOne(['id' => $name]);
        } else {
            $card = model\EntityTable::findOne(['name' => $name]);
        }

        return $card ? ArrayHelper::getValue($card, 'title', '') : '';
    }

    /**
     * Получиь техническое имя по id.
     *
     * @param int $id Идентификатор карточки
     *
     * @return string
     */
    public static function getName($id)
    {
        $card = model\EntityTable::find()->where('id', $id)->fields('name')->asArray()->getOne();

        return $card['name'] ?? '';
    }

    /**
     * Получиь id  по техническому имени.
     *
     * @param string $name Техническое имя карточки
     *
     * @return int
     */
    public static function getId($name)
    {
        if (is_int($name)) {
            return $name;
        }
        $card = model\EntityTable::find()->where('name', $name)->fields('id')->asArray()->getOne();

        return isset($card['id']) ? (int) $card['id'] : '';
    }

    /**
     * Имя базовой карточки для $card.
     *
     * @param int|string $card Идентификатор карточки
     *
     * @return string
     */
    public static function getBaseCard($card)
    {
        if (!isset(self::$cache[$card])) {
            $oExtCard = self::get($card);

            if (!$oExtCard) {
                self::setCache($card);
            }

            if (!$oExtCard->isExtended()) {
                self::setCache($oExtCard->id, $card);
                self::setCache($oExtCard->name, $card);
            }

            if (!isset(self::$cache[$oExtCard->parent])) {
                $oBaseCard = self::get($oExtCard->parent);

                if (!$oBaseCard) {
                    self::setCache($oExtCard->id);
                    self::setCache($oExtCard->name);
                } else {
                    self::setCache($oExtCard->id, $oBaseCard->name);
                    self::setCache($oExtCard->name, $oBaseCard->name);
                    self::setCache($oBaseCard->id, $oBaseCard->name);
                    self::setCache($oBaseCard->name, $oBaseCard->name);
                }
            } else {
                return self::getCache($oExtCard->parent);
            }
        }

        return self::getCache($card);
    }

    /**
     * Получение объекта поля.
     *
     * @param null|int $id
     *
     * @return model\FieldRow
     */
    public static function getField($id = null)
    {
        if (!$id) {
            return model\FieldTable::getNewRow();
        }

        return model\FieldTable::find($id);
    }

    /**
     * Добавление нового поля для сущности.
     *
     * @param int $card Идентификатор карточки
     * @param string $name Техническое имя поля
     * @param string $title Заголовок поля
     * @param string $editor Тип редактора для поля
     * @param int $group Идентификатор группы поля
     *
     * @return model\FieldRow
     */
    public static function setField($card, $name, $title, $editor = ft\Editor::STRING, $group = 0)
    {
        $oField = model\FieldTable::getNewRow();

        $oField->setData([
            'name' => $name,
            'title' => $title,
            'group' => $group,
            'editor' => $editor,
            'entity' => $card,
        ]);

        $oField->save();

        return $oField;
    }

    /**
     * Добавление новой группы полей для сущности.
     *
     * @param string $name Техническое имя поля
     * @param string $title Заголовок поля
     *
     * @return model\FieldGroupTable
     */
    public static function setGroup($name, $title)
    {
        $oGroup = model\FieldGroupTable::getNewRow();

        // Высчитать позицию последней группы
        $aLastPos = \Yii::$app->getDb()->createCommand(
            '
                SELECT MAX(`position`)
                FROM ' . model\FieldGroupTable::getTableName()
        )->query()->read();

        $oGroup->setData([
            'name' => $name,
            'title' => $title,
            'position' => (int) reset($aLastPos) + 1,
        ]);

        $oGroup->save();

        return $oGroup;
    }

    /**
     * Получение объекта поля по имени и идетификатору карточки.
     *
     * @param int $card идентификатор карточки
     * @param string $name имя поля
     *
     * @return model\FieldRow
     */
    public static function getFieldByName($card, $name)
    {
        return model\FieldTable::findOne(['entity' => $card, 'name' => $name]);
    }

    /**
     * Получение списка всех полей связанных с коллекциями.
     *
     * @return array
     */
    public static function getCollectionFields()
    {
        $coll = Collection::getCollectionList();

        $out = [];

        if (!count($coll)) {
            return $out;
        }

        $fields = model\FieldTable::find()->where('link_id', array_keys($coll))->getAll();

        foreach ($fields as $field) {
            $out[$field->link_id . ':' . $field->name] = $coll[$field->link_id] . ' (' . $field->title . ')';
        }

        return $out;
    }

    /**
     * Получение карточки для поля.
     *
     * @param $name
     *
     * @return int
     */
    public static function get4Field($name)
    {
        $query = model\FieldTable::find()->where(['name' => $name]);

        /** @var FieldRow $row */
        if ($row = $query->each()) {
            return $row->entity;
        }

        return false;
    }

    /**
     * Получение карточки для поля по link_id.
     *
     * @param $name
     * @param mixed $id
     *
     * @return int
     */
    public static function get4FieldByEntityId($id)
    {
        $query = model\FieldTable::find()->where(['link_id' => $id]);
        /** @var FieldRow $row */
        if ($row = $query->each()) {
            return $row->entity;
        }

        return false;
    }

    /**
     * Получение списка групп карточки.
     *
     * @return model\FieldGroupRow[]
     */
    public static function getGroups()
    {
        return model\FieldGroupTable::find()->order('position')->getAll();
    }

    /**
     * Получение списка заголовоков групп
     *
     * @param array $aGroupsTypes Ссылка на типы групп
     *
     * @return array
     */
    public static function getGroupList(&$aGroupsTypes = [])
    {
        $aOut = [0 => \Yii::t('catalog', 'base_group')];

        /** @var model\FieldGroupRow $oGroup */
        $query = model\FieldGroupTable::find()
            ->order('position');

        while ($oGroup = $query->each()) {
            $aOut[$oGroup->id] = $oGroup->title;
            $aGroupsTypes[$oGroup->id] = (int) $oGroup->group_type;
        }

        return $aOut;
    }

    /**
     * Получение объекта группы поля.
     *
     * @param null|int $id идентификатор группы
     *
     * @return model\FieldGroupRow
     */
    public static function getGroup($id = null)
    {
        if (!$id) {
            return model\FieldGroupTable::getNewRow();
        }

        return model\FieldGroupTable::find($id);
    }

    /**
     * Получение объекта группы поля по имени и идетификатору карточки.
     *
     * @param string $name имя группы
     *
     * @return model\FieldGroupRow
     */
    public static function getGroupByName($name)
    {
        return model\FieldGroupTable::findOne(['name' => $name]);
    }

    /**
     * Отдает флаг скрытости детальной.
     *
     * @param mixed $iSectionId
     *
     * @return bool
     */
    public static function isDetailHidden($iSectionId)
    {
        $sCard = Section::getDefCard($iSectionId);

        return self::isDetailHiddenByCard($sCard);
    }

    public static function isDetailHiddenByCard($sCard)
    {
        $oCard = ft\Cache::get($sCard);

        if (!$oCard) {
            return false;
        }

        return (bool) $oCard->getHideDetail();
    }

    public static function getPaymentObject()
    {
        $value = SysVar::get(self::PREFIX_PAYMENT_OBJECT_NAME . self::DEF_BASE_CARD);
        if ($value) {
            return $value;
        }

        return \skewer\build\Tool\Payments\Api::DEFAULT_PAYMENT_OBJECT;
    }
}
