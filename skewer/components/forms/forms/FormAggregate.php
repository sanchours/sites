<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\base\ui\ARSaveException;
use skewer\components\config\Exception;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\entities\FormExtraDataEntity;
use skewer\components\forms\entities\FormLinkEntity;
use skewer\components\forms\service\FieldService;
use yii\base\UserException;

/**
 * Class FieldAggregate.
 *
 * @property FieldAggregate[] $fields
 * @property SettingsForm $settings
 * @property ProtectionForm $protection
 * @property HandlerTypeForm $handler
 * @property TargetForm $target
 * @property LicenseForm $license
 * @property AnswerForm $answer
 * @property TypeResultPageForm $result
 */
class FormAggregate extends InternalFormsAggregate
{
    /** @var int $idForm */
    public $idForm;

    /** @var null|FormEntity */
    private $_formEntity;

    private $_settings;
    private $_protection;
    private $_handler;
    private $_target;
    private $_license;
    private $_answer;
    private $_result;

    private $_fieldService;

    /**
     * FieldAggregate constructor.
     *
     * @param null|int $idForm
     * @param array $config
     *
     * @throws UserException
     */
    public function __construct($idForm = null, array $config = [])
    {
        $this->idForm = (int) $idForm;

        $this->setEntity();

        if ($this->_formEntity->id) {
            $attributes = $this->_formEntity->getAttributes();
            $this->setSettings($attributes);
            $this->setProtection(
                (bool) $this->_formEntity->captcha,
                (bool) $this->_formEntity->hide_field,
                (bool) $this->_formEntity->block_js
            );

            $this->_fieldService = new FieldService($this->idForm);
        }

        parent::__construct($config);
    }

    protected function getInternalForms(): array
    {
        return [
            $this->settings->getShortNameObject() => [],
            $this->handler->getShortNameObject() => [
                'type' => 'handler_type',
                'value' => 'handler_value',
                'title' => '',
            ],
            $this->protection->getShortNameObject() => [],
            $this->target->getShortNameObject() => [
                'yandex' => 'target_yandex',
                'google' => 'target_google',
            ],
            $this->license->getShortNameObject() => [
                'text' => '',
            ],
            $this->answer->getShortNameObject() => [
                'title' => '',
                'letter' => '',
            ],
            $this->result->getShortNameObject() => [
                'type' => 'type_result_page',
                'text' => '',
                'link' => '',
            ],
        ];
    }

    /**
     * Список форм, содержащих дополнительные сущности для сохранения.
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    protected function getExtraDataForms(): array
    {
        return [
            $this->license->getShortNameObject(),
            $this->answer->getShortNameObject(),
            $this->result->getShortNameObject(),
        ];
    }

    /**
     * @return bool
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(): bool
    {
        $oFormLinkTableSchema = \Yii::$app->db->getTableSchema(
            FormLinkEntity::tableName(),
            true
        );

        if ($oFormLinkTableSchema) {
            FormLinkEntity::deleteAll(['form_id' => $this->idForm]);
        }

        foreach ($this->fields as $field) {
            assert($field instanceof FieldAggregate);
            if (!$field->delete()) {
                throw new Exception("Не удалось удалить поле \"{$field->title}\"");
            }
        }

        FormExtraDataEntity::deleteAll(['form_id' => $this->idForm]);

        return (bool) $this->_formEntity->delete();
    }

    protected function getEntity(): FormEntity
    {
        return $this->_formEntity;
    }

    /**
     * @throws UserException
     */
    private function setEntity()
    {
        if ($this->idForm) {
            $this->_formEntity = FormEntity::getById($this->idForm);
            if (!$this->_formEntity) {
                throw new UserException("Form №{$this->idForm} not found in db.");
            }
        } else {
            $this->_formEntity = new FormEntity();
        }
    }

    private function setSettings(array $attributes = [])
    {
        $this->_settings = new SettingsForm($attributes);
    }

    public function getSettings(): SettingsForm
    {
        if (!isset($this->_settings)) {
            if ($this->_formEntity->id) {
                $this->setSettings($this->_formEntity->getAttributes());
            } else {
                $this->setSettings();
            }
        }

        return $this->_settings;
    }

    private function setProtection(
        bool $captcha = false,
        bool $hideField = false,
        bool $blockJs = true
    ) {
        $this->_protection = new ProtectionForm($captcha, $hideField, $blockJs);
    }

    public function getProtection(): ProtectionForm
    {
        if (!isset($this->_protection)) {
            if ($this->_formEntity->id) {
                $this->setProtection(
                    $this->_formEntity->captcha,
                    $this->_formEntity->hide_field,
                    $this->_formEntity->block_js
                );
            } else {
                $this->setProtection();
            }
        }

        return $this->_protection;
    }

    //установка значений из entity
    private function setHandler(
        string $handlerType = HandlerTypeForm::HANDLER_TO_BASE,
        string $handlerValue = ''
    ) {
        $this->_handler = new HandlerTypeForm($handlerType, $handlerValue);
    }

    public function getHandler(): HandlerTypeForm
    {
        if (!isset($this->_handler)) {
            if ($this->_formEntity->id) {
                $this->setHandler(
                    $this->_formEntity->handler_type,
                    $this->_formEntity->handler_value
                );
            } else {
                $this->setHandler();
            }
        }

        return $this->_handler;
    }

    //установка значений из entity
    private function setTarget($yandex = '', $google = '')
    {
        $this->_target = new TargetForm($yandex, $google);
    }

    public function getTarget(): TargetForm
    {
        if (!isset($this->_target)) {
            if ($this->_formEntity->id) {
                $this->setTarget(
                    $this->_formEntity->target_yandex,
                    $this->_formEntity->target_google
                );
            } else {
                $this->setTarget();
            }
        }

        return $this->_target;
    }

    public function setLicense()
    {
        $this->_license = new LicenseForm($this->idForm);
    }

    public function getLicense()
    {
        if (!isset($this->_license)) {
            $this->setLicense();
        }

        return $this->_license;
    }

    public function setResult()
    {
        $this->_result = new TypeResultPageForm($this->idForm);
    }

    /**
     * @return TypeResultPageForm
     */
    public function getResult(): TypeResultPageForm
    {
        if (!isset($this->_result)) {
            $this->setResult();
        }

        return $this->_result;
    }

    public function setAnswer()
    {
        $this->_answer = new AnswerForm($this->idForm);
    }

    public function getAnswer()
    {
        if (!isset($this->_answer)) {
            $this->setAnswer();
        }

        return $this->_answer;
    }

    /**
     * @throws UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            throw new UserException(current($this->getFirstErrors()));
        }

        if ($this->_formEntity === null) {
            $this->setEntity();
        }

        foreach ($this->getInternalForms() as $formName => $fields) {
            $this->_formEntity->setAttributes(
                $this->{$formName}->getBasicPropertiesForEntity(),
                true,
                $fields
            );
        }

        if ($this->_formEntity->validate() === false) {
            throw new ARSaveException($this->_formEntity);
        }

        return $this->_formEntity->save();
    }

    public function saveExtraData(): bool
    {
        if ($this->_formEntity === null) {
            $this->setEntity();
        }

        $success = true;

        foreach ($this->getExtraDataForms() as $formName) {
            assert($this->{$formName} instanceof ExtraDataForm);
            $success = $success && $this->{$formName}->save($this->_formEntity->id);
        }

        return $success;
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return FieldAggregate[]
     */
    public function getFields(): array
    {
        return $this->_fieldService->getFields();
    }

    public function hasSetSystemForm(): bool
    {
        return $this->_formEntity->isAttributeChanged('system')
            && $this->_formEntity->system;
    }

    public function getIdForm()
    {
        if (!$this->idForm) {
            $this->idForm = $this->_formEntity->id;
        }

        return $this->idForm;
    }

    /**
     * @throws \ReflectionException
     *
     * @return string
     */
    public function getScriptTargetsInForm(): string
    {
        return $this->target->buildScriptTargetsInForm();
    }

    /**
     * Установка параметров для вывода в шаблон.
     *
     * @param string $formHash
     * @param TemplateForm $template
     */
    public function setFormDisplayOptions(
        string $formHash,
        TemplateForm &$template
    ) {
        $requiredFields = $this->hasRequiredFields();

        foreach ($this->getInternalForms() as $formName => $fields) {
            /* @var InternalForm $this ->$formName */
            $this->{$formName}->setFormDisplayOptions(
                $formHash,
                $template,
                $requiredFields
            );
        }
    }

    /**
     * Валидация переданных параметров во внутренние компоненты.
     *
     * @param array $innerData
     * @param string $formHash
     *
     * @return bool
     */
    public function validateInnerData(array $innerData, string $formHash): bool
    {
        $success = true;

        foreach ($this->getInternalForms() as $formName => $fields) {
            $success = $success && $this->{$formName}->validateInnerData(
                $innerData,
                $formHash
                );
        }

        return $success;
    }

    public function getLastModifyData(): string
    {
        return $this->_formEntity->last_modified_date;
    }

    private function hasRequiredFields(): bool
    {
        foreach ($this->fields as $field) {
            if ($field->settings->required) {
                return true;
            }
        }

        return false;
    }
}
