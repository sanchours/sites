<?php

declare(strict_types=1);

namespace skewer\build\Tool\Forms;

use skewer\base\site\Site;
use skewer\base\site\Type;
use skewer\base\ui;
use skewer\build\Tool\Forms\view\AddLink;
use skewer\build\Tool\Forms\view\Agreed;
use skewer\build\Tool\Forms\view\Answer;
use skewer\build\Tool\Forms\view\CreateForm;
use skewer\build\Tool\Forms\view\CrmFieldList;
use skewer\build\Tool\Forms\view\CrmIntegrationEdit;
use skewer\build\Tool\Forms\view\CrmLinkEdit;
use skewer\build\Tool\Forms\view\EditField;
use skewer\build\Tool\Forms\view\EditForm;
use skewer\build\Tool\Forms\view\Fields;
use skewer\build\Tool\Forms\view\Index;
use skewer\build\Tool\Forms\view\LinkList;
use skewer\build\Tool\Forms\view\SettingsResultPage;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\build\Tool\Policy\Module as ModulePolicy;
use skewer\components\auth\CurrentAdmin;
use skewer\components\forms\Api as ApiForm;
use skewer\components\forms\ApiField;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\TypeFieldForm;
use skewer\components\forms\service\CatalogFormService;
use skewer\components\forms\service\CrmFormService;
use skewer\components\forms\service\FieldService;
use skewer\components\forms\service\FormService;
use yii\base\UserException;

/**
 * Class Module.
 */
class Module extends ModulePrototype
{
    /** @var int $idCurrentForm */
    public $idCurrentForm = 0;
    public $enableSettings = 0;

    /** @var FieldService $_fieldService */
    private $_fieldService;

    /** @var FormService $_formService */
    private $_formService;

    /** @var CrmFormService $_crmFormService */
    private $_crmFormService;

    /** @var CatalogFormService $_catalogFormService */
    private $_catalogFormService;

    protected function preExecute()
    {
        // id текущего раздела
        $this->idCurrentForm = (int) $this->getInDataVal(
            'idForm',
            $this->getInt('idForm')
        );

        $this->installService();
    }

    private function installService()
    {
        $this->_fieldService = new FieldService($this->idCurrentForm);
        $this->_formService = new FormService();
        $this->_crmFormService = new CrmFormService();
        $this->_catalogFormService = new CatalogFormService();
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     */
    protected function actionInit()
    {
        $iInitParam = (int) $this->get('init_param');

        if ($iInitParam) {
            $this->idCurrentForm = $iInitParam;
            $this->installService();
            $this->actionFields();
        } else {
            $this->actionForms();
        }
    }

    protected function actionForms()
    {
        $forms = $this->_formService->getForms(true);

        $this->render(new Index([
            'forms' => $forms,
        ]));
    }

    protected function actionCreateForm()
    {
        $systemMode = CurrentAdmin::isSystemMode();

        $hasFormTarget = (
            $systemMode || CurrentAdmin::canDo(
                ModulePolicy::className(),
                'useFormsReachGoals'
            )
        );

        $types = HandlerTypeForm::getHandlerTypes();
        if (!$systemMode && isset($types['toMethod'])) {
            unset($types['toMethod']);
        }

        $form = $this->_formService->createForm();

        $this->render(new CreateForm([
            'form' => $this->_formService->combineFormInOneArray($form),
            'systemMode' => $systemMode,
            'typesHandler' => $types,
            'handlerSubtext' => \Yii::t('forms', 'form_handler_value_default', [
                Site::getAdminEmail(),
            ]),
            'hasFormTarget' => $hasFormTarget,
        ]));

        return psComplete;
    }

    /**
     * Редактирование параметров формы.
     *
     * @return int
     */
    protected function actionEditForm()
    {
        $form = $this->_formService->getFormById($this->idCurrentForm);

        $systemMode = CurrentAdmin::isSystemMode();

        $hasFormTarget = (
            $systemMode || CurrentAdmin::canDo(
                ModulePolicy::className(),
                'useFormsReachGoals'
            )
        );

        $types = HandlerTypeForm::getHandlerTypes();
        if (isset($types['toMethod'])) {
            unset($types['toMethod']);
        }

        $this->render(new EditForm([
            'form' => $this->_formService->combineInOneArray(
                $form->getFullObject()
            ),
            'typesHandler' => $types,
            'handlerSubtext' => \Yii::t('forms', 'form_handler_value_default', [
                Site::getAdminEmail(),
            ]),
            'hasFormTarget' => $hasFormTarget,
            'editableHandlerType' => !$form->handler->canNotEditType(
                (bool) $form->settings->system
            ),
        ]));

        return psComplete;
    }

    /**
     * Сохранение параметров формы.
     */
    protected function actionSave()
    {
        try {
            $idForm = $this->_formService->save(
                $this->getInData()
            );

            if (!$this->idCurrentForm) {
                $this->idCurrentForm = $idForm;
                $this->installService();
            }

            $this->actionFields();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Удаление формы.
     */
    protected function actionDelete()
    {
        try {
            if (!$this->idCurrentForm) {
                throw new \Exception('Not found form id');
            }

            $form = $this->_formService->getFormById($this->idCurrentForm);

            if ($form->settings->system) {
                throw new \Exception(\Yii::t('forms', 'no_del_sys_form'));
            }

            $form->delete();

            $this->actionForms();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Клонирование формы.
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    protected function actionClone()
    {
        if ($this->_formService->cloneForm($this->idCurrentForm)) {
            $this->addMessage(\Yii::t('forms', 'from_cloned'));
        } else {
            $this->addError('From not found.');
        }

        $this->actionForms();
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     */
    protected function actionFields()
    {
        $fields = $this->_formService->combineFieldsForShow(
            $this->_fieldService->getFields()
        );

        $this->render(new Fields([
            'fields' => $fields,
            'hasCatalog' => Type::hasCatalogModule(),
            'hasCRM' => $this->_crmFormService->isInstall(),
        ]));
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     */
    protected function actionSortFieldList()
    {
        $this->_fieldService->sortFields(
            $this->getInData(),
            $this->get('dropData'),
            $this->get('position')
        );
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return int
     */
    protected function actionEditField(): int
    {
        $innerData = $this->getInData();

        $idField = isset($innerData['idField'])
            ? (int) $innerData['idField']
            : null;

        $fieldForm = $idField === null
            ? $this->_fieldService->create()
            : $this->_fieldService->getField($idField);

        if (!$fieldForm) {
            throw new UserException(\Yii::t('forms', 'field_not_found'));
        }

        $this->render(new EditField([
            'fieldTypes' => $fieldForm->type->getTypes(),
            'typesOfValidation' => $fieldForm->type->getTypesValid(),

            'settings' => $this->_fieldService->combineInOneArray(
                $fieldForm->getFullObject()
            ),

            'maxSizeOfFileSending' => ApiField::getUploadMaxSize(), //todo переделать

            'typesShow' => $fieldForm->getType()->getFieldObject()->getDisplayTypes(),
            'noSize' => !$fieldForm->getType()->getFieldObject()->isEditSizeDB(),
        ]));

        return psComplete;
    }

    /**
     * todo необходимо сделать отдельную механику.
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    protected function actionChangeType()
    {
        $typeNew = $this->normalizeTypeOfValidByFieldsParams($this->get('formData'));
        $nameOldType = $this->get('fieldOldValue');

        $fieldForm = $this->_fieldService->changeType($typeNew, $nameOldType);

        if ($fieldForm->type->warning) {
            $this->addWarning(
                $fieldForm->type->warning['title'],
                $fieldForm->type->warning['message']
            );
        }

        $this->render(new EditField([
            'fieldTypes' => $fieldForm->type->getTypes(),
            'typesOfValidation' => $fieldForm->getType()->getTypesValid(),

            'settings' => $this->_fieldService->combineInOneArray(
                $fieldForm->getFullObject()
            ),
            'maxSizeOfFileSending' => ApiField::getUploadMaxSize(),
            'typesShow' => $fieldForm->type->getFieldObject()->getDisplayTypes(),
            'noSize' => !$fieldForm->getType()->getFieldObject()->isEditSizeDB(),
        ]));

        return psComplete;
    }

    /**
     * @throws UserException
     * @throws \ReflectionException
     */
    protected function actionSaveField()
    {
        if (!$this->_fieldService->save($this->getInData())) {
            throw new UserException('Не удалось сохранить информацию о поле');
        }

        $this->actionFields();
    }

    /**
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\db\StaleObjectException
     */
    protected function actionDeleteField()
    {
        $this->_fieldService->delete($this->getInData());

        $this->actionFields();
    }

    protected function actionAnswer()
    {
        $answer = $this->_formService->getAnswer($this->idCurrentForm);
        $title = $this->_formService
            ->getFormById($this->idCurrentForm)
            ->settings
            ->title;

        $this->setPanelName(
            \Yii::t('forms', 'answer_head') . " \"{$title}\""
        );

        $this->render(new Answer([
            'answer' => $answer,
            'linkAutoReply' => $this->getTextWithLink(),
        ]));

        return psComplete;
    }

    /**
     * Получение уведомления о расположении настройки текста автоответа.
     *
     * @return string
     */
    private function getTextWithLink()
    {
        $formEntity = ApiForm::getChildClassEntity($this->_formService->getFormById($this->idCurrentForm));
        if ($formEntity) {
            $linkAutoReply = $formEntity->getLinkAutoReply();
            if ($linkAutoReply) {
                $textAboutLink = \Yii::t('forms', 'answer_notification', [$linkAutoReply]);

                return $textAboutLink;
            }
        }

        return '';
    }

    /**
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function actionAnswerSave()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception('Не был передан идентификатор формы.');
        }

        $this->_formService->saveAnswer(
            $this->idCurrentForm,
            $this->getInData()
        );

        $this->actionFields();
    }

    /**
     * Форма редактирования лицензионного соглашения.
     */
    protected function actionAgreed()
    {
        $form = $this->_formService->getFormById($this->idCurrentForm);

        $license = $this->_formService->getLicense($this->idCurrentForm);

        $this->setPanelName(
            \Yii::t('forms', 'agreed_head') . " \"{$form->settings->title}\""
        );

        $this->render(new Agreed(['license' => $license]));

        return psComplete;
    }

    /**
     * Сохранение лицензионного соглашения.
     *
     * @throws \Exception
     */
    protected function actionAgreedSave()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception('Не был передан идентификатор формы.');
        }

        $this->_formService->saveLicense(
            $this->idCurrentForm,
            $this->getInData()
        );

        $this->actionFields();
    }

    /**
     * Список полей формы связанных с каталогом
     *
     * @throws \Exception
     */
    protected function actionLinkList()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception('Не был передан идентификатор формы.');
        }

        $this->render(new LinkList([
            'links' => $this->_catalogFormService->getLinksByForm(
                $this->idCurrentForm
            ),
        ]));
    }

    /**
     * Форма добавление связи.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionAddLink()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception('Не был передан идентификатор формы.');
        }

        $this->render(new AddLink([
            'formFields' => $this->_catalogFormService->getFieldsByFormSlugTitle(
                $this->idCurrentForm
            ),
            'cardFields' => $this->_catalogFormService->getFieldsBaseCard(),
        ]));

        return psComplete;
    }

    /**
     * @throws \Exception
     */
    protected function actionSaveLink()
    {
        $data = $this->getInData();

        if (!$this->idCurrentForm) {
            $this->addError(\Yii::t('forms', 'error_form_not_found'));
        }
        if (!isset($data['form_field']) || empty($data['form_field'])) {
            $this->addError(\Yii::t('forms', 'error_empty_form_field'));
        }
        if (!isset($data['card_field']) || empty($data['card_field'])) {
            $this->addError(\Yii::t('forms', 'error_empty_card_field'));
        }

        if ($this->getErrors()) {
            return;
        }

        $this->_catalogFormService->addFieldLink($this->idCurrentForm, $data);

        $this->actionLinkList();
    }

    /**
     * @throws \Exception
     */
    protected function actionDelLink()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception(\Yii::t('forms', 'error_form_not_found'));
        }

        $this->_catalogFormService->deleteFieldLink(
            $this->getInDataValInt('link_id'),
            $this->idCurrentForm
        );

        $this->actionLinkList();
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    protected function actionCRMIntegration()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception(\Yii::t('forms', 'error_form_not_found'));
        }

        $form = $this->_formService->getFormById($this->idCurrentForm);

        $this->render(new CrmIntegrationEdit([
            'form' => $this->_formService->combineInOneArray(
                $form->getFullObject()
            ),
        ]));

        return psComplete;
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    protected function actionCrmLinkList()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception(\Yii::t('forms', 'error_form_not_found'));
        }

        $form = $this->_formService->getFormById($this->idCurrentForm);

        $fields = $this->_crmFormService->getLinkForField($form);

        if ($this->_crmFormService->messageWarning) {
            $this->addMessage(
                \Yii::t('crm', 'warning_message1'),
                \Yii::t('crm', 'warning_message2'),
                10000
            );
        }

        $this->render(new CrmFieldList(['fields' => $fields]));

        return psComplete;
    }

    protected function actionEditCrmLink()
    {
        $form = $this->_formService->getFormById($this->idCurrentForm);
        $systemForm = $form->settings->system;

        $fields = [];
        /* @var FieldAggregate[] $aRows */
        foreach ($form->fields as $field) {
            $fields[$field->id] = $systemForm
                ? \Yii::tSingleString($field->title)
                : $field->title;
        }

        $this->render(new CrmLinkEdit([
            'fields' => $fields,
            'link' => $this->getInData(),
        ]));

        return psComplete;
    }

    /**
     * @throws \Exception
     * @throws \yii\db\Exception
     *
     * @return int
     */
    protected function actionSaveCrmLink()
    {
        $link = $this->_crmFormService->saveLink($this->getInData());

        if ($link->fieldId) {
            $field = $this->_fieldService->getField($link->fieldId);
            $field->settings->required = $link->required;
            $field->save();
        }

        return $this->actionCrmLinkList();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'idForm' => $this->idCurrentForm,
            'enableSettings' => $this->enableSettings,
        ]);
    }

    /** Редактирование настроек результирующей страницы формы */
    protected function actionEditResultPage()
    {
        $form = $this->_formService->getFormById($this->idCurrentForm);

        $resultPage = $this->_formService->getResultPage($this->idCurrentForm);

        $this->setPanelName(
            \Yii::t('forms', 'settings_result_page_head') . " \"{$form->settings->title}\""
        );

        $this->render(new SettingsResultPage([
            'resultPage' => $resultPage,
        ]));

        return psComplete;
    }

    /** Ajax-обновление формы редактирования настроек результирующей страницы */
    public function actionUpdateSettingResultPageForm()
    {
        $data = $this->get('formData', []);

        $this->render(new SettingsResultPage(['resultPage' => $data]));
    }

    /**
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function actionSaveSettingResultPage()
    {
        if (!$this->idCurrentForm) {
            throw new \Exception('Не был передан идентификатор формы.');
        }

        $this->_formService->saveResultPage(
            $this->idCurrentForm,
            $this->getInData()
        );

        $this->actionFields();
    }

    /**
     * Если у параметров поля установлен невалидный type_typeOfValid
     * то метод устанавливает валидный (первый из списка)
     *
     * @param array $fieldsParams
     * @return array
     * @throws UserException
     * @throws \ReflectionException
     */
    private function normalizeTypeOfValidByFieldsParams(array $fieldsParams): array
    {
        $typeNewField = new TypeFieldForm($fieldsParams['type_name']);

        if (!$typeNewField->getFieldObject()->hasTypeOfValid(
            $fieldsParams['type_typeOfValid']
        )) {
            $fieldsParams['type_typeOfValid'] = $typeNewField->getDefaultTypeValid();
        }

        return $fieldsParams;
    }

} //class
