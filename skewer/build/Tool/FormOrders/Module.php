<?php

namespace skewer\build\Tool\FormOrders;

use skewer\base\site_module;
use skewer\base\ui;
use skewer\build\Tool;
use skewer\build\Tool\FormOrders\view\Edit;
use skewer\build\Tool\FormOrders\view\Error;
use skewer\build\Tool\FormOrders\view\ListForm;
use skewer\build\Tool\FormOrders\view\ShowForms;
use skewer\components\auth\CurrentAdmin;
use skewer\components\forms\entities\FormOrderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\service\FormOrderService;
use skewer\components\forms\service\FormSectionService;
use skewer\components\forms\service\FormService;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class Module extends Tool\LeftList\ModulePrototype implements site_module\SectionModuleInterface
{
    public $iCurrentForm = 0;

    /** @var int id раздела */
    protected $sectionId = 0;

    /** @var int id формы */
    protected $formId = 0;

    protected $onPage = 20;
    protected $iPageNum = 0;

    /** @var FormService $_formService */
    private $_formService;

    /** @var FormSectionService $_formSectionService */
    private $_formSectionService;

    /** @var FormOrderService $_formOrderService */
    private $_formOrderService;

    const FIELDS_COUNT_FOR_SHOW = 3;

    /**
     * Сообщает используется ли только одна форма для отображения.
     *
     * @return bool
     */
    private function useOneForm()
    {
        return (bool) $this->formId;
    }

    public function sectionId()
    {
        return $this->sectionId;
    }

    protected function preExecute()
    {
        $this->iCurrentForm = (int) $this->getInDataVal(
            'idForm',
            $this->getInt('idForm')
        );

        if (!$this->iCurrentForm) {
            $this->iCurrentForm = $this->getEnvParam('idForm');
        }

        $this->reinstallService();
    }

    public function reinstallService()
    {
        $this->_formService = new FormService();
        $this->_formSectionService = new FormSectionService($this->sectionId());

        $this->_formOrderService = new FormOrderService($this->iCurrentForm);
    }

    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        $oIface->setServiceData([
            'idForm' => $this->iCurrentForm,
        ]);
    }

    /**
     * @throws \Exception
     *
     * @return int|void
     */
    public function actionInitTab()
    {
        $forms = $this->_formSectionService->getHandlerBaseFormsForSection();

        if ($this->sectionId && empty($forms)) {
            return psBreak;
        }
        parent::actionInitTab();
    }

    /**
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return int
     */
    protected function actionInit()
    {
        if ($idRecord = $this->initParam()) {
            return $this->actionEdit($idRecord);
        }

        $this->iPageNum = $this->getInt('page');

        if ($this->sectionId()) {
            $forms = $this->_formSectionService->getHandlerBaseFormsForSection();

            if (empty($forms)) {
                return psBreak;
            }

            $ids = ArrayHelper::getColumn($forms, 'idForm');

            if (count($ids) == 1) {
                //1 форма... ее выводим
                $this->iCurrentForm = $this->formId = $ids[0];
                $this->reinstallService();
                $this->actionList();
            } elseif (count($ids) > 1) {
                //Несколько форм... выводим их список для выбора
                $this->actionShowForms($forms);
            }
        } else {
            $idForm = $this->get('idForm');
            if ($idForm) {
                $this->actionList();
            } else {
                $this->actionShowForms();
            }
        }
    }

    private function initParam()
    {
        $iInitParam = $this->get('init_param');
        if ($iInitParam) {
            $aInitParam = explode('_', $iInitParam);
            $idForm = $aInitParam[0];
            $idRecord = (isset($aInitParam[1])) ? $aInitParam[1] : '';
            $this->iCurrentForm = $idForm;
            $this->setInnerData('id', $idRecord);

            return $idRecord;
        }

        return false;
    }

    /**
     * Список форм с заказами.
     *
     * @param array $forms
     *
     * @throws \Exception
     */
    protected function actionShowForms(array $forms = [])
    {
        if ($this->sectionId()) {
            /*Вызов произошел из таба раздела*/
            if (empty($forms)) {
                $forms = $this->_formSectionService->getHandlerBaseFormsForSection();
            }

            $forms = $this->_formService->combineFormsInOneArray(
                $forms
            );
        } else {
            /*Вызов из Tool*/
            $forms = $this->_formSectionService->getAllHandlerBaseForms();
        }

        $this->iCurrentForm = 0;

        $this->setPanelName(\Yii::t('forms', 'form_list'));

        $this->render(new ShowForms([
            'forms' => $forms,
        ]));
    }

    /**
     * Список заказов.
     *
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function actionList()
    {
        $data = $this->getInData();

        $idFilter = $this->getStr(
            'filter_id',
            $this->getInnerData('filter_id', null)
        );

        $personFilter = $this->getStr(
            'filter_person',
            $this->getInnerData('filter_person', null)
        );

        $this->iCurrentForm = $data['idForm']
            ?? $this->iCurrentForm;

        if (!$this->iCurrentForm) {
            throw new \Exception('form not found');
        }

        $form = $this->_formService->getFormById($this->iCurrentForm);

        if ($form->handler->type !== HandlerTypeForm::HANDLER_TO_BASE) {
            $this->render(new Error([
                'error' => \Yii::t('forms', 'bad_status'),
            ]));

            return;
        }

        if ($idFilter === '') {
            $idFilter = null;
        }

        $formOrders = $this->_formOrderService->getOrderByFilter(
            $this->onPage,
            $this->iPageNum,
            $idFilter,
            $personFilter
        );

        $fields = $form->getFields();

        $iFieldCounter = 0;
        $fieldsForList = [];
        foreach ($fields as $field) {
            assert($field instanceof FieldAggregate);
            if ($field->type->getFieldObject()->skipOnList()) {
                continue;
            }

            ++$iFieldCounter;
            if ($iFieldCounter > self::FIELDS_COUNT_FOR_SHOW) {
                break;
            }

            $fieldsForList[] = $field;
        }

        foreach ($fields as $field) {
            assert($field instanceof FieldAggregate);
            $slug = $field->settings->slug;
            if (isset($formOrders[0][$slug])) {
                foreach ($formOrders as &$value) {
                    $value[$slug] = $field->type->getFieldObject()->getTrueValue(
                        $value[$slug],
                        $field->type->default
                    );
                }
            }
        }

        $this->setPanelName(\Yii::t('forms', 'list_orders_from_form', [
            $form->settings->title,
        ]));

        $this->render(new ListForm([
            'fields' => $fieldsForList,
            'formOrders' => $formOrders,
            'filter' => [
                'id' => $idFilter,
                'person' => $personFilter,
            ],
            'onPage' => $this->onPage,
            'page' => $this->iPageNum,
            'total' => $this->_formOrderService->ordersCount,
            'notUseOneForm' => !$this->useOneForm(),
        ]));
    }

    /**
     * @param int $idRecord
     *
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return int
     */
    protected function actionEdit($idRecord = 0)
    {
        // -- обработка данных
        $aData = $this->getInData();
        $id = $aData['id'] ?? ($idRecord) ?: 0;

        if (!$this->iCurrentForm) {
            throw new \Exception('item not found');
        }

        $form = $this->_formService->getFormById($this->iCurrentForm);

        $formOrder = $id
            ? (new FormOrderEntity($this->iCurrentForm))->getFieldById($id)
            : [];

        $fields = $this->_formSectionService->getFieldsForFormOrder(
            $form,
            $formOrder
        );

        $this->render(new Edit([
            'fields' => $fields,
            'statusList' => FormOrderEntity::getStatusList(),
            'formOrder' => $formOrder,
            'bCanDelete' => $id,
        ]));

        return psComplete;
    }

    protected function actionDelete()
    {
        try {
            // -- обработка данных
            $aData = $this->getInData();
            $idOrder = $aData['id'] ?? 0;

            if (!$idOrder || !$this->iCurrentForm) {
                throw new \Exception('item not found');
            }
            $formAggregate = $this->_formService->getFormById($this->iCurrentForm);
            foreach ($formAggregate->getFields() as $field) {
                $field->type->getFieldObject()->deleteExtraData(
                    $this->iCurrentForm,
                    $formAggregate->settings->slug,
                    $idOrder
                );
            }

            $this->_formOrderService->deleteFormOrderByIds($idOrder);

            // вывод списка
            $this->actionList();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    protected function actionDelAllOrders()
    {
        try {
            if (!$this->iCurrentForm) {
                throw new \Exception('item not found');
            }

            $this->_formOrderService->deleteAllFormOrders();

            // вывод списка
            $this->actionInit();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    protected function actionDeleteMultiple()
    {
        $aData = $this->get('data');
        if ($aData['items'] && $this->iCurrentForm) {
            $ids = ArrayHelper::getColumn($aData['items'], 'id');

            $this->_formOrderService->deleteMultipleFormOrderByIds($ids);
        }
        $this->actionInit();
    }

    /**
     * Сохранение заказы.
     *
     * @throws UserException
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function actionSave()
    {
        // -- обработка данных
        $formOrder = $this->getInData();

        if (!$this->iCurrentForm) {
            throw new \Exception('item not found');
        }

        if (isset($formOrder['id']) && $formOrder['id']) {
            $this->_formOrderService->updateFormOrder(
                (int) $formOrder['id'],
                $formOrder
            );
        } else {
            $this->_formOrderService->insertFormOrder($formOrder);
        }

        $this->actionList();
    }

    protected function checkAccess()
    {
        // для разделов
        if ($this->sectionId()) {
            // проверить права доступа
            if (!CurrentAdmin::canRead($this->sectionId())) {
                throw new UserException('accessDenied');
            }
        } else {
            // иначе это панель управления - стандартная проверка
            parent::checkAccess();
        }
    }
}
