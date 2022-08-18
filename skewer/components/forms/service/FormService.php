<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\base\section\Parameters;
use skewer\components\auth\CurrentAdmin;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\forms\AnswerForm;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\LicenseForm;
use skewer\components\forms\forms\TypeResultPageForm;
use skewer\components\forms\traits\ParserExtJsTrait;
use yii\base\UserException;

class FormService
{
    use ParserExtJsTrait;

    public function createForm(): FormAggregate
    {
        return new FormAggregate();
    }

    /**
     * @param int $id
     * @return FormAggregate
     * @throws UserException
     */
    public function getFormById(int $id): FormAggregate
    {
        return new FormAggregate($id);
    }

    /**
     * @param string $slug
     *
     * @return null|FormAggregate
     */
    public function getFormByName(string $slug)
    {
        $formEntity = FormEntity::getBySlug($slug);
        if ($formEntity instanceof FormEntity) {
            return new FormAggregate($formEntity->id);
        }
    }

    public function combineFormInOneArray(FormAggregate $formAggregate)
    {
        return $this->combineInOneArray(
            $formAggregate->getFullObject()
        );
    }

    /**
     * @param array $formsAggregate
     *
     * @return \Generator
     */
    public function combineFormsInOneArray(array $formsAggregate)
    {
        foreach ($formsAggregate as $formAggregate) {
            assert($formAggregate instanceof FormAggregate);
            yield $this->combineInOneArray(
                $formAggregate->getFullObject()
            );
        }
    }

    public function hasFormWithSlug(string $slug): bool
    {
        return FormEntity::hasFormWithSlug($slug);
    }

    public function hasFormById(int $id = null): bool
    {
        return $id === null ? false : FormEntity::hasFormById($id);
    }

    public function getForms($bUseAllForms = false)
    {
        $queryForm = FormEntity::find()
            ->select(['id'])
            ->orderBy(['system' => SORT_ASC, 'id' => SORT_ASC]);

        if (!$bUseAllForms && !CurrentAdmin::isSystemMode()) {
            $queryForm
                ->where(['!=', 'handler_type', HandlerTypeForm::HANDLER_TO_METHOD])
                ->andWhere(['system' => false]);
        }

        $formsId = $queryForm->all();
        foreach ($formsId as $form) {
            yield $this->combineInOneArray(
                (new FormAggregate($form->getAttribute('id')))
                    ->getFullObject()
            );
        }
    }

    public function combineFieldsForShow(array $fields)
    {
        foreach ($fields as $fieldAggregator) {
            assert($fieldAggregator instanceof FieldAggregate);
            yield $this->combineInOneArray(
                $fieldAggregator->getFullObject()
            );
        }
    }

    /**
     * @param array $innerData
     *
     * @throws UserException
     *
     * @return int
     */
    public function save(array $innerData): int
    {
        $dataMultipleForm = $this->parseGluedArray($innerData);

        $idForm = isset($innerData['idForm']) ? (int) $innerData['idForm'] : null;

        $form = new FormAggregate($idForm);
        $form->setAttributes($dataMultipleForm);

        if (!$form->save()) {
            throw new UserException($form->getFirstErrors());
        }

        if ($idForm && $form->hasSetSystemForm()) {
            Parameters::deactivateFormInTrees($idForm);
        }

        if ($idForm === null) {
            $form->saveExtraData();
        }

        return $form->getIdForm();
    }

    public function getAnswer(int $idForm): array
    {
        $answer = new AnswerForm($idForm);

        return $answer->getBasicProperties();
    }

    /**
     * @param int $idForm
     * @param array $innerData
     *
     * @throws UserException
     *
     * @return bool
     */
    public function saveAnswer(int $idForm, array $innerData): bool
    {
        $formAggregate = $this->getFormById($idForm);
        $formAggregate->answer->setAttributes($innerData);

        return $formAggregate->save() && $formAggregate->saveExtraData();
    }

    public function getLicense(int $idForm): array
    {
        $licenseForm = new LicenseForm($idForm);

        return $licenseForm->getBasicProperties();
    }

    public function saveLicense(int $idForm, array $innerData): bool
    {
        $formAggregate = $this->getFormById($idForm);
        $formAggregate->license->setAttributes($innerData);

        return $formAggregate->save() && $formAggregate->saveExtraData();
    }

    public function getResultPage(int $idForm): array
    {
        $resultType = new TypeResultPageForm($idForm);

        return $resultType->getBasicProperties();
    }

    /**
     * @param int $idForm
     * @param array $innerData
     *
     * @throws UserException
     *
     * @return bool
     */
    public function saveResultPage(int $idForm, array $innerData): bool
    {
        $formAggregate = $this->getFormById($idForm);
        $formAggregate->result->setAttributes($innerData);

        return $formAggregate->save() && $formAggregate->saveExtraData();
    }

    /**
     * @param int $idForm
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return null|int
     */
    public function cloneForm(int $idForm)
    {
        $form = $this->getFormById($idForm);
        $formFullData = $form->getFullObject();
        unset($formFullData['idForm']);

        $formClone = new FormAggregate();
        $formClone->setAttributes($formFullData);
        $formClone->settings->title .= ' (clone)';
        if ($formClone->save()) {
            foreach ($form->fields as $field) {
                assert($field instanceof FieldAggregate);
                $this->cloneField($field, $formClone->getIdForm());
            }

            $formClone->saveExtraData();

            return $formClone->getIdForm();
        }
    }

    /**
     * @param FieldAggregate $fieldAggregator
     * @param int $idForm
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return bool
     */
    private function cloneField(FieldAggregate $fieldAggregator, int $idForm)
    {
        $fieldSubForms = $fieldAggregator->getFullObject();
        unset($fieldSubForms['idField'], $fieldSubForms['idForm']);

        $fieldClone = new FieldAggregate($idForm);
        $fieldClone->setAttributes($fieldSubForms);
        $fieldClone->entity->detachBehaviors();

        return $fieldClone->save();
    }
}
