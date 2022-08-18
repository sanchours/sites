<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\components\forms\components\SortFields;
use skewer\components\forms\entities\FieldEntity;
use skewer\components\forms\entities\FormOrderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\TypeFieldForm;
use skewer\components\forms\traits\ParserExtJsTrait;
use yii\base\UserException;

class FieldService
{
    use ParserExtJsTrait;

    private $_idForm;

    public function __construct(int $idForm)
    {
        $this->_idForm = $idForm;
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return FieldAggregate
     */
    public function create(): FieldAggregate
    {
        return new FieldAggregate($this->_idForm);
    }

    /**
     * @param array $newTypeData
     * @param string $nameOldType
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return FieldAggregate
     */
    public function changeType(
        array $newTypeData,
        string $nameOldType
    ): FieldAggregate {
        $dataMultipleForm = $this->parseGluedArray($newTypeData);

        $idField = (int) $newTypeData['idField'] ?? 0;
        $fieldAggregator = $this->getField($idField);
        $fieldAggregator->setAttributes($dataMultipleForm);
        $fieldAggregator->type->setName($dataMultipleForm['type']['name']);

        $oldType = new TypeFieldForm($nameOldType);

        if ($fieldAggregator->idField && (
            $fieldAggregator->type->getFieldObject()->getTypeDB() !== $oldType->getFieldObject()->getTypeDB()
            )) {
            $fieldAggregator->type->setWarning(
                \Yii::t('forms', 'field_change_warning_title'),
                \Yii::t('forms', 'field_change_warning_lost')
            );

            return $fieldAggregator;
        }

        if ($fieldAggregator->type->getFieldObject()->hasExtraFile) {
            $fieldAggregator->type->setWarning(
                \Yii::t('forms', 'field_change_warning_title'),
                \Yii::t('forms', 'field_change_warning')
            );
        }

        return $fieldAggregator;
    }

    /**
     * @param array $innerData
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return bool
     */
    public function save(array $innerData): bool
    {
        $dataMultipleForm = $this->parseGluedArray($innerData);

        $idField = (int) $innerData['idField'] ?? null;

        $fieldForm = new FieldAggregate($this->_idForm, $idField);
        $fieldForm->setAttributes($dataMultipleForm);

        if (!$fieldForm->save()) {
            throw new UserException($fieldForm->getFirstErrors());
        }

        if ($fieldForm->hasChangedType()) {
            $fieldForm->clearExtraData();
        }

        return (new FormOrderEntity($this->_idForm))->updateEntity();
    }

    /**
     * @param array $formData
     *
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\db\StaleObjectException
     *
     * @return bool
     */
    public function delete(array $formData): bool
    {
        $fieldAggregator = $this->getFieldFormFromData(($formData));

        if (!$fieldAggregator->idField || !$fieldAggregator->entity) {
            throw new UserException('Не удалось найти поле формы');
        }

        if (!$fieldAggregator->delete()) {
            throw new UserException('Не удалось удалить поле формы');
        }

        $fieldAggregator->type->deleteExtraData(
            $fieldAggregator->idForm,
            $fieldAggregator->idField
        );

        return (new FormOrderEntity($this->_idForm))->updateEntity();
    }

    /**
     * @param array $dragData
     * @param array $dropData
     * @param $position
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function sortFields(array $dragData, array $dropData, $position)
    {
        $dragField = $this->getFieldFormFromData($dragData);
        $targetField = $this->getFieldFormFromData($dropData);

        $sortFields = new SortFields($dragField, $targetField, $position);

        if (!$sortFields->sort()) {
            throw new UserException(\Yii::t('forms', 'sortParamError'));
        }
    }

    /**
     * @param int $idField
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return FieldAggregate
     */
    public function getField(int $idField)
    {
        return new FieldAggregate($this->_idForm, $idField);
    }

    /**
     * @param array $formData
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return FieldAggregate
     */
    private function getFieldFormFromData(array $formData): FieldAggregate
    {
        $multipleForm = $this->parseGluedArray($formData);

        $fieldForm = new FieldAggregate(
            $this->_idForm,
            $multipleForm['idField']
        );
        $fieldForm->setAttributes($multipleForm);

        return $fieldForm;
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return array
     */
    public function getFields()
    {
        $fields = FieldEntity::getFieldsByIdForm($this->_idForm);
        $fieldAggregators = [];

        if ($fields) {
            /** @var FieldEntity $fieldEntity */
            foreach ($fields as $fieldEntity) {
                $fieldForm = new FieldAggregate($this->_idForm);
                $fieldForm->setEntity($fieldEntity);
                $fieldForm->settings->title = \Yii::tSingleString(
                    $fieldForm->settings->title
                );
                $fieldAggregators[$fieldForm->settings->slug] = $fieldForm;
            }
        }

        return $fieldAggregators;
    }

    public function getTypeFieldByPath(string $pathByClass): string
    {
        $serviceTypeField = new TypeFieldService();

        return $serviceTypeField->getNameByPath($pathByClass);
    }

    public function getTypeOfValidByPath(string $pathByClass): string
    {
        $service = new TypeOfValidService();

        return $service->getNameByPath($pathByClass);
    }
}
