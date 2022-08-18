<?php

declare(strict_types=1);

namespace skewer\components\forms\entities;

use skewer\base\ft\Entity;
use skewer\base\orm\Query;
use skewer\base\orm\state\StateInsert;
use skewer\base\orm\state\StateSelect;
use skewer\base\orm\state\StateUpdate;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\service\FormService;
use yii\helpers\ArrayHelper;

/**
 * Class FormOrderEntity
 * класс для работы с заказами из форм
 *
 * @property int $date
 * @property int $status
 */
class FormOrderEntity
{
    private $formService;

    private $formAggregator;

    const NEW_STATUS_ORDER = 'new';
    const READY_STATUS_ORDER = 'ready';

    /**
     * Набор статусов для заказов из форм
     *
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::NEW_STATUS_ORDER => \Yii::t('forms', 'status_new'),
            self::READY_STATUS_ORDER => \Yii::t('forms', 'status_ready'),
        ];
    }

    public function __construct(int $idForm)
    {
        $this->formService = new FormService();

        $this->formAggregator = $this->formService->getFormById($idForm);
    }

    public function getTableName(): string
    {
        return "frm_{$this->formAggregator->settings->slug}";
    }

    public function getEntity(): Entity
    {
        return Entity::get(self::getTableName())
            ->clear();
    }

    public function saveEntity(Entity $entity): bool
    {
        return $entity->save()->build() instanceof Entity;
    }

    public function addFieldInEntity(
        FieldAggregate $field,
        Entity $entity
    ): Entity {
        $fieldType = $field->type->maxLength
            ? "{$field->type->title}({$field->type->maxLength})"
            : $field->type->title;

        $entity->addField(
            $field->settings->slug,
            $fieldType,
            $field->settings->title
        );

        return $entity;
    }

    public function dropColumnFromEntity(string $nameColumn): bool
    {
        return (bool) Query::SQL(
            "ALTER TABLE `{$this->getTableName()}` 
                    DROP COLUMN `{$nameColumn}`"
        )->rowsCount();
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function updateEntity()
    {
        $model = $this->getEntity();

        $fields = $this->formAggregator->getFields();

        /** @var FieldAggregate $field */
        foreach ($fields as $field) {
            $fieldType = $field->type->maxLength && !$field->type->isFile()
                ? "{$field->type->getFieldObject()->getTypeDB()}({$field->type->maxLength})"
                : $field->type->getFieldObject()->getTypeDB();

            if ($fieldType !== null) {
                $model->addField(
                    $field->settings->slug,
                    $fieldType,
                    $field->settings->title
                );
            }
        }

        $model
            ->addField('__add_date', 'date', 'Дата добавления')
            ->addField('__status', 'varchar(255)', 'Статус')
            ->addField('__section', 'varchar(255)', 'Раздел');

        return $model->save()->build() instanceof Entity;
    }

    public function clearFieldValue(string $fieldName): bool
    {
        return Query::UpdateFrom($this->getTableName())
            ->set($fieldName, '')
            ->get();
    }

    /**
     * @param string $fieldName
     *
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public function hasFieldByName(string $fieldName): bool
    {
        return Query::SQL(
            "SHOW COLUMNS FROM `{$this->getTableName()}` LIKE '{$fieldName}' ;"
            )->fetchArray() !== false;
    }

    public function deleteFieldById(int $id): bool
    {
        return (bool) Query::DeleteFrom($this->getTableName())
            ->where('id', $id)
            ->get();
    }

    public function deleteMultipleByIds(array $ids): bool
    {
        return (bool) Query::DeleteFrom($this->getTableName())
            ->where('id IN ?', $ids)
            ->get();
    }

    public function clearTable(): bool
    {
        return (bool) Query::DeleteFrom($this->getTableName())->get();
    }

    public function selectFrom(): StateSelect
    {
        return Query::SelectFrom($this->getTableName());
    }

    public function getFieldById(int $id)
    {
        return Query::SelectFrom($this->getTableName())
            ->where('id', $id)
            ->getOne();
    }

    /**
     * @param array $formOrder
     * @param null|int $idSection
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool|int
     */
    public function insertFormOrder(
        array $formOrder,
        int $idSection = null
    ) {
        $query = Query::InsertInto($this->getTableName())
            ->set('__status', self::NEW_STATUS_ORDER)
            ->set('__add_date', date('Y-m-d'));

        if ($idSection !== null) {
            $query->set('__section', $idSection);
        }

        $this->setParamsOrderInQuery($query, $formOrder);

        return $query->get();
    }

    /**
     * @param int $id
     * @param array $formOrder
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function updateFormOrder(int $id, array $formOrder): bool
    {
        $query = Query::UpdateFrom($this->getTableName())
            ->where('id', $id)
            ->set(
                '__status',
                $formOrder['__status']
                ?? self::NEW_STATUS_ORDER
            );

        $this->setParamsOrderInQuery($query, $formOrder);

        return (bool) $query->get();
    }

    /**
     * @param StateInsert|StateUpdate $query
     * @param array $paramsOrder
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    private function setParamsOrderInQuery(&$query, array $paramsOrder)
    {
        $fields = $this->formAggregator->getFields();

        /** @var FieldAggregate $field */
        foreach ($fields as $field) {
            $value = ArrayHelper::getValue(
                $paramsOrder,
                $field->settings->slug,
                null
            );
            if ($value !== null) {
                $query->set($field->settings->slug, $value);
            }
        }
    }
}
