<?php

namespace skewer\components\forms\forms;

use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\entities\FormExtraDataEntity;

abstract class ExtraDataForm extends InternalForm
{
    private $idForm;
    /** @var FormExtraDataEntity $_extraEntity */
    protected $_extraEntity;
    /** @var FormEntity $_formEntity */
    protected $_formEntity;

    protected $_extraFields = [];

    public function __construct(int $idForm = null, array $config = [])
    {
        if ($idForm) {
            $this->setIdForm($idForm);
        }

        parent::__construct($config);
    }

    /**
     * @param int $idForm
     */
    public function setIdForm(int $idForm)
    {
        $this->idForm = $idForm;

        $this->_formEntity = FormEntity::getById($this->idForm);

        $form = FormExtraDataEntity::getByFormId($this->idForm);

        if ($form instanceof FormExtraDataEntity) {
            $this->setExtraEntity($form);
        }
    }

    /**
     * @param mixed $extra_entity
     */
    final public function setExtraEntity(FormExtraDataEntity $extra_entity = null)
    {
        $this->_extraEntity = $extra_entity;
    }

    /**
     * @return mixed
     */
    final public function getExtraEntity(): FormExtraDataEntity
    {
        if (!isset($this->_extraEntity)) {
            $form = FormExtraDataEntity::getByFormId($this->idForm);

            if ($form === null) {
                $form = new FormExtraDataEntity($this->idForm);
            }

            $this->setExtraEntity($form);
        }

        return $this->_extraEntity;
    }

    final public function save(int $idForm): bool
    {
        $this->setIdForm($idForm);
        $extraEntity = $this->getExtraEntity();
        foreach ($this->_extraFields as $titleFieldForm => $titleFieldEntity) {
            $extraEntity->{$titleFieldEntity} = $this->{$titleFieldForm};
        }
        $this->_extraEntity = $extraEntity;

        return $extraEntity->save();
    }
}
