<?php

namespace skewer\components\catalog\model;

use skewer\base\ft;
use skewer\base\orm;
use skewer\components\catalog\Entity;
use skewer\helpers\Transliterate;

class EntityRow extends orm\ActiveRecord
{
    public $id = 0;
    public $name = '';
    public $title = '';
    public $module = '';
    public $type = 0;
    public $in_base = true;
    public $desc = '';
    public $hide_detail = '';
    public $parent = '';
    public $cache = '';
    public $priority_sort = 0;

    public function getTableName()
    {
        return 'c_entity';
    }

    public function initSave()
    {
        $this->checkUniqueName();
        \Yii::$app->router->updateModificationDateSite();

        return parent::initSave();
    }

    public function preDelete()
    {
        $query = FieldTable::find()->where('entity', $this->id);
        while ($row = $query->each()) {
            $row->delete();
        }

        // Выполнить метод обновления кэша сущности для удаления более не нужных таблиц связей "многие ко многим"
        $this->updCache();

        if ($this->type == Entity::TypeBasic) {
            GoodsTable::removeCard($this->id);
            SectionTable::removeCard($this->id);
        }

        if ($this->type == Entity::TypeExtended) {
            if ($oBaseCard = EntityTable::find($this->parent)) {
                EntityTable::removeRowBaseByCard($oBaseCard->name, $this->name);
            }

            GoodsTable::removeCard(0, $this->id);
            SectionTable::removeCard(0, $this->id);
        }

        return true;
    }

    public function delete()
    {
        parent::delete();

        // удаление таблицы с данными
        $oModel = $this->getModelFromCache();

        $sTableName = $oModel->getTableName();

        // Удаление таблицы
        orm\Query::dropTable($sTableName);

        return true;
    }

    /**
     * Получение объекта модели из кеша.
     *
     * @return ft\Model
     */
    public function getModelFromCache()
    {
        if (!$this->cache) {
            $this->save();
            $this->updCache();
        }

        $oJson = new ft\converter\Json();

        return $oJson->dataToFtModel($this->cache);
    }

    /**
     * Получение списка полей карточки.
     *
     * @return FieldRow[]
     */
    public function getFields()
    {
        return FieldTable::find()->where('entity', $this->id)->order('position')->getAll();
    }

    public function updCache()
    {
        $sPreffix = Entity::getTablePreffix($this->type);

        // заголовок новой модели
        $oFtEntity =
            ft\Entity::get($this->name)
                ->clear()
                ->setTablePrefix($sPreffix)
                ->setTableType(ft\DBTable::TypeInnoDb)
                ->setParentId($this->parent)
                ->setType($this->type)
                ->setPrioritySort($this->priority_sort);

        if ($this->id) {
            $oFtEntity->setEntityId($this->id);
        }

        $oFtEntity->setHideDetail($this->hide_detail);

        /** Старая модель из кэша перед обновлением (только если существует) */
        $oFtEntityLast = ($this->cache) ? $this->getModelFromCache() : null;
        /** Список и настройки полей перед обновлением новых */
        $aFieldsLast = ($oFtEntityLast) ? $oFtEntityLast->getFileds() : [];
        /** Список найденных полей при сохранении новых */
        $aFoundFields = [];

        /* Сортировка полей по группам */
        $sFieldTableName = FieldTable::getTableName();
        $sFieldGroupTableName = FieldGroupTable::getTableName();

        $query = FieldTable::find()
            ->fields("{$sFieldTableName}.*")
            ->join('left', $sFieldGroupTableName, $sFieldGroupTableName, "{$sFieldTableName}.group = {$sFieldGroupTableName}.id")
            ->where("{$sFieldTableName}.entity = ?", $this->id)
            ->order("{$sFieldGroupTableName}.position")
            ->order("{$sFieldTableName}.position");

        /** @var FieldRow $oField */
        while ($oField = $query->each()) {
            // добавляем поле
            $oFtEntity->addFieldObject($oField->getFTObject());

            // обработка полей со связями
            if ($oField->link_id and $oField->link_type) { // link_type может быть пустым, а link_id нет (см. \skewer\components\catalog\model\FieldRow::save())
                /** @var EntityRow $oRelationEntity */
                $oRelationEntity = EntityTable::find($oField->link_id);

                if (!$oRelationEntity) {
                    throw new \Exception(sprintf('Не найдена связанная сущность при сохранении поля "%s" (%s)', $oField->title, $oField->name));
                }
                $oFtEntity->addRelation($oField->link_type, $oRelationEntity->name, $oField->name, 'id', 'id');
            }

            // Добавление индекса для системных полей
            if (in_array($oField->name, EntityTable::getSystemFieldNames())) {
                $oFtEntity->selectField($oField->name);
                $oFtEntity->addIndex('index', $oField->name);
            }

            // Очистка/удаление старых таблиц связей при смене типа поля, типа связи или сущности связи
            if (isset($aFieldsLast[$oField->name]) and ($oRelationLast = $aFieldsLast[$oField->name]->getFirstRelation()) and
                 ($oRelationLast->getType() == ft\Relation::MANY_TO_MANY)) {
                // Если у поля есть таблица связей, то проверить её на изменение
                if (($oRelationNew = $oFtEntity->getModel()->getFiled($oField->name)->getFirstRelation()) and
                     ($oRelationNew->getType() == ft\Relation::MANY_TO_MANY)) {
                    // Если изменён тип связи или связываемая сущность, то очистить прошлые связи
                    if (($oRelationLast->getType() != $oRelationNew->getType()) or
                         ($oRelationLast->getEntityName() != $oRelationNew->getEntityName())) {
                        $aFieldsLast[$oField->name]->clearLinkTable();
                    }
                } else {
                    // Если поле теперь без связи, то удалить таблицу связи
                    $aFieldsLast[$oField->name]->removeLinkTable();
                }
            }
            // Запомнить обработанное поле
            $aFoundFields[$oField->name] = true;
        }

        // Очистка таблиц связей удалённых полей
        foreach ($aFieldsLast as $oFieldLast) {
            if (!isset($aFoundFields[$oFieldLast->getName()]) and ($oRelationLast = $oFieldLast->getFirstRelation()) and
                 ($oRelationLast->getType() == ft\Relation::MANY_TO_MANY)) {
                $oFieldLast->removeLinkTable();
            }
        }

        // пересоздаем модель
        $oFtEntity->addDefaultProcessorSet()
            ->save()
            ->build();

        $oConverter = new ft\converter\Json();

        $this->cache = $oConverter->ftModelToData($oFtEntity->getModel());

        $this->save();
        \Yii::$app->router->updateModificationDateSite();
    }

    private function checkUniqueName()
    {
        if (!$this->name) {
            $this->name = $this->title;
        }

        $name = Transliterate::genSysName($this->name, 'card');

        if (mb_strlen($name) > 60) {
            $name = mb_substr($name, 0, 57);
        }

        $i = '';
        do {
            $this->name = $name . $i;
            $res = EntityTable::findOne(['name' => $this->name, 'id<>?' => $this->id]);
            ++$i;
        } while ($res);

        return true;
    }

    /**
     * Отдает флаг того, что сущность является расширяющей.
     *
     * @return bool
     */
    public function isExtended()
    {
        return (int) $this->type === Entity::TypeExtended;
    }

    /**
     * Отдает флаг того, что сущность является базовой.
     *
     * @return bool
     */
    public function isBasic()
    {
        return (int) $this->type === Entity::TypeBasic;
    }

    /**
     * Отдает флаг того, что сущность является базовой.
     *
     * @return bool
     */
    public function isSimple()
    {
        return (int) $this->type === Entity::TypeDictionary;
    }
}
