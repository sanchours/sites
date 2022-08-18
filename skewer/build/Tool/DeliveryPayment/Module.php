<?php

namespace skewer\build\Tool\DeliveryPayment;

use skewer\base\SysVar;
use skewer\base\ui\ARSaveException;
use skewer\build\Tool\LeftList\ModulePrototype;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 *  Class Module.
 */
class Module extends ModulePrototype
{
    // текущий номер страницы ( с 0, а приходит с 1 )
    public $iPageNum = 0;
    // число элементов на страниц
    public $iOnPage = 20;

    protected function preExecute()
    {
        // номер страницы
        $this->iPageNum = $this->getInt('page');
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Выводит список элементов.
     */
    public function actionList()
    {
        $data = ['paid_delivery' => Api::isDeliveryPaid()];
        $this->render(new view\Index(['data' => $data]));
    }

    /**
     * Метод, меняющий на лету значения.
     */
    protected function actionPaidDelivery()
    {
        $aFormData = $this->get('formData', []);

        if (isset($aFormData['paid_delivery'])) {
            SysVar::set(Api::PAID_DELIVERY, $aFormData['paid_delivery']);
        }

//        $data = ['paid_delivery'=>SysVar::get(Api::PAID_DELIVERY)];
//        $paidDelivery = new view\Index([
//            'data' => $data
//        ]);
//        $paidDelivery->build();
//        $this->setInterfaceUpd($paidDelivery->getInterface());

        //не можем обойти ошибку с перегрузкой интерфейса,
        //пока сделали просто перегрузку старницы,
        // в этом интерфейсе это не так критично
        $this->actionList();
    }

    /**
     * Выводит список элементов из сущности AR TypeDelivery.
     */
    public function actionTypeDeliveryList()
    {
        $query = models\TypeDelivery::find()
            ->with('payments')
            ->limit($this->iOnPage)
            ->offset($this->iPageNum * $this->iOnPage);
        $query->orderBy('priority');

        $aItems = $query->asArray()->all();
        $iCount = models\TypeDelivery::find()->count();

        $this->setPanelName(\Yii::t('deliverypayment', 'list_typedelivery'));
        $this->render(new view\TypeDeliveryIndex([
            'aItems' => $aItems,
            'page' => $this->iPageNum,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
            'paidDelivery' => Api::isDeliveryPaid(),
            'aEditFields' => Api::getEditFieldsTypeDdelivery(),
        ]));
    }

    /**
     * Выводит форму для добавления/редактирования объекта AR TypeDelivery.
     */
    public function actionTypeDeliveryForm()
    {
        $aData = $this->getInData();

        if (isset($aData['id'])) {
            $oTypeDelivery = models\TypeDelivery::findOne(['id' => $aData['id']]);
            $sPanelName = (\Yii::t('deliverypayment', 'edit_item_typedelivery', $oTypeDelivery->title));
        } else {
            $oTypeDelivery = models\TypeDelivery::getNewRow();
            $sPanelName = (\Yii::t('deliverypayment', 'new_item_typedelivery'));
        }

        //собираем данные для формы
        $data = $oTypeDelivery->getAttributes();
        $data['payments'] = $oTypeDelivery->getDeliveryPayment();

        $this->setPanelName($sPanelName);
        $this->render(new view\TypeDeliveryForm([
            'item' => $oTypeDelivery,
            'data' => $data,
            'aPayments' => models\TypePayment::getPayments(),
            'paidDelivery' => Api::isDeliveryPaid(),
            ]));
    }

    /**
     * Добавление/сохранение изменений объекта AR TypeDelivery.
     *
     * @throws ARSaveException
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionTypeDeliverySave()
    {
        // запросить данные
        $aData = $this->get('data', []);
        $iId = $this->getInDataValInt('id');

        // Новая запись?
        $bIsNewRecord = !(bool) $iId;

        if (!$bIsNewRecord) {
            if (!($oTypeDeliveryRow = models\TypeDelivery::findOne(['id' => $iId]))) {
                throw new UserException(\Yii::t('deliverypayment', 'error_row_not_found', [$iId]));
            }
        } else {
            $oTypeDeliveryRow = models\TypeDelivery::getNewRow();
            $aData[Api::FIELD_SORT] = Api::getMaxPriority(models\TypeDelivery::tableName());
            if ($aData['alias'] === '') {
                $aData['alias'] = $aData['title'];
            }
        }

        if (!$oTypeDeliveryRow->saveWithLink($aData)) {
            throw new ARSaveException($oTypeDeliveryRow);
        }

        $this->actionTypeDeliveryList();
    }

    /**
     * Метод сохранения значения поля для типа оплаты (редактирование из списка).
     *
     * @throws UserException
     */
    protected function actionTypeDeliveryFastSave()
    {
        $data = $this->get('data');
        $field = $this->get('field_name');

        $iId = ArrayHelper::getValue($data, 'id', 0);
        $value = ArrayHelper::getValue($data, $field, '');

        if (!($oTypePayment = models\TypeDelivery::findOne(['id' => $iId]))) {
            throw new UserException(\Yii::t('deliverypayment', 'error_row_not_found', [$iId]));
        }

        $oTypePayment->saveWithLink([$field => (int) $value]);

        Api::updFastList($oTypePayment, $this);
    }

    /**
     * Удаление объекта(ов) AR TypeDelivery.
     *
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionTypeDeliveryDelete()
    {
        // запросить данные
        $iItemId = $this->getInDataValInt('id', 0);

        if (!($oTypeDelivery = models\TypeDelivery::findOne($iItemId))) {
            throw new UserException(\Yii::t('deliverypayment', 'error_row_not_found', [$iItemId]));
        }

        $oTypeDelivery->deleteWithLink();
        // вывод списка
        $this->actionTypeDeliveryList();
    }

    public function actionTypeDeliverySort()
    {
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        $iDropId = $aDropData['id'] ?? false;
        $aItems = [$this->getInDataValInt('id', 0)];

        if (!count($aItems) || !$iDropId || !$sPosition) {
            $this->addError(\Yii::t('deliverypayment', 'error_sort'));
        }

        if ($sPosition == 'after') {
            $aItems = array_reverse($aItems);
        }

        foreach ($aItems as $iSelectId) {
            Api::sortItems(new models\TypeDelivery(), $iSelectId, $iDropId, $sPosition);
        }
    }

    /**
     * Выводит список элементов из сущности AR TypePayment.
     */
    public function actionTypePaymentList()
    {
        $query = models\TypePayment::find()
            ->limit($this->iOnPage)
            ->offset($this->iPageNum * $this->iOnPage);
        $query->orderBy('priority');

        $aItems = $query->asArray()->all();
        $iCount = models\TypePayment::find()->count();

        $this->setPanelName(\Yii::t('deliverypayment', 'list_typepayment'));
        $this->render(new view\TypePaymentIndex([
            'aItems' => $aItems,
            'page' => $this->iPageNum,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
        ]));
    }

    /**
     * Выводит форму для добавления/редактирования объекта AR TypePayment.
     */
    public function actionTypePaymentForm()
    {
        $aData = $this->getInData();

        if (isset($aData['id'])) {
            $oTypePayment = models\TypePayment::findOne(['id' => $aData['id']]);
            $sPanelName = (\Yii::t('deliverypayment', 'edit_item_typepayment', $oTypePayment->title));
        } else {
            $oTypePayment = models\TypePayment::getNewRow();
            $sPanelName = (\Yii::t('deliverypayment', 'new_item_typepayment'));
        }

        $aPaymentSystems = ArrayHelper::map(\skewer\build\Tool\Payments\Api::getPaymentsList(true), 'type', 'title');

        $this->setPanelName($sPanelName);
        $this->render(new view\TypePaymentForm(['item' => $oTypePayment, 'aPaymentSystems' => $aPaymentSystems]));
    }

    /**
     * Добавление/сохранение изменений объекта AR TypePayment.
     *
     * @throws UserException
     * @throws \yii\db\Exception
     */
    public function actionTypePaymentSave()
    {
        // запросить данные
        $aData = $this->get('data', []);
        $iId = $this->getInDataValInt('id');

        // Новая запись?
        $bIsNewRecord = !(bool) $iId;

        if (!$bIsNewRecord) {
            if (!($oTypePayment = models\TypePayment::findOne(['id' => $iId]))) {
                throw new UserException(\Yii::t('deliverypayment', 'error_row_not_found', [$iId]));
            }
        } else {
            $oTypePayment = models\TypePayment::getNewRow();
            $aData[Api::FIELD_SORT] = Api::getMaxPriority(models\TypePayment::tableName());
            if ($aData['alias'] === '') {
                $aData['alias'] = $aData['title'];
            }
        }

        // Заполняем запись данными из web-интерфейса
        $oTypePayment->setAttributes($aData);
        $oTypePayment->prohibDeactivate();

        if (!$oTypePayment->save()) {
            throw new ARSaveException($oTypePayment);
        }

        $this->actionTypePaymentList();
    }

    /**
     * Метод сохранения значения поля для типа оплаты (редактирование из списка).
     *
     * @throws UserException
     */
    protected function actionTypePaymentFastSave()
    {
        $data = $this->get('data');
        $field = $this->get('field_name');

        $iId = ArrayHelper::getValue($data, 'id', 0);
        $value = ArrayHelper::getValue($data, $field, '');

        if (!($oTypePayment = models\TypePayment::findOne(['id' => $iId]))) {
            throw new UserException(\Yii::t('deliverypayment', 'error_row_not_found', [$iId]));
        }

        if (!$value) {
            if ($aDelivery = $oTypePayment->checkLink()) {
                Api::updFastList($oTypePayment, $this);
                throw new UserException('Тип оплаты выбран в способах доставки: ' . implode(',', $aDelivery));
            }
        }

        $oTypePayment->setAttribute($field, (int) $value);
        $oTypePayment->save();

        Api::updFastList($oTypePayment, $this);
    }

    /**
     * Удаление объекта(ов) AR TypePayment.
     *
     * @throws UserException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionTypePaymentDelete()
    {
        // запросить данные
        $iItemId = $this->getInDataValInt('id', 0);

        if (!($oTypePayment = models\TypePayment::findOne($iItemId))) {
            throw new UserException(\Yii::t('deliverypayment', 'error_row_not_found', [$iItemId]));
        }

        $oTypePayment->prohibDeactivate();
        $oTypePayment->delete();

        // вывод списка
        $this->actionTypePaymentList();
    }

    public function actionTypePaymentSort()
    {
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        $iDropId = $aDropData['id'] ?? false;
        $aItems = [$this->getInDataValInt('id', 0)];

        if (!count($aItems) || !$iDropId || !$sPosition) {
            $this->addError(\Yii::t('deliverypayment', 'error_sort'));
        }

        if ($sPosition == 'after') {
            $aItems = array_reverse($aItems);
        }

        foreach ($aItems as $iSelectId) {
            Api::sortItems(new models\TypePayment(), $iSelectId, $iDropId, $sPosition);
        }
    }
}
