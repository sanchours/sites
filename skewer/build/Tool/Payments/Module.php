<?php

namespace skewer\build\Tool\Payments;

use skewer\base\SysVar;
use skewer\build\Tool;

/**
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /** @var string Заглушка для пароля */
    private $pwdFake = '--------';

    protected function actionInit()
    {
        $this->actionList();
    }

    protected function actionList()
    {
        $aItems = Api::getPaymentsList();

        $this->render(new Tool\Payments\view\Index([
            'aItems' => $aItems,
        ]));

        return psComplete;
    }

    protected function actionEdit()
    {
        $sType = $this->getInDataVal('type');

        $aPaymentsFields = Api::getFields4PaymentType($sType);

        if (!$aPaymentsFields) {
            $this->actionList();
        }

        $aFields = ['active', 'test_life'];

        foreach ($aPaymentsFields as $aField) {
            $aFields[] = $aField[0];
        }

        $aItems = Api::getParams($sType, $aFields);

        if (!$aItems) {
            $aItems = [];
        }

        $aItems['type'] = $sType;

        $this->render(new Tool\Payments\view\Edit([
            'aPaymentsFields' => $aPaymentsFields,
            'sType' => $sType,
            'aItems' => $aItems,
        ]));
    }

    /**
     * Сохранение активности из списка.
     *
     * @return int
     */
    protected function actionSaveActive()
    {
        $aType = $this->getInDataVal('type');

        if (!$aType or !Api::existType($aType)) {
            return $this->actionList();
        }

        $oParam = ar\Params::getParam($aType, 'active');
        $oParam->value = $this->getInDataVal('value');
        $oParam->save();

        return $this->actionList();
    }

    protected function actionSave()
    {
        $aType = $this->getInDataVal('type');

        if (!$aType or !Api::existType($aType)) {
            return $this->actionList();
        }

        $aData = $this->getInData();
        unset($aData['type']);

        if ($aData) {
            foreach ($aData as $sKey => $sVal) {
                $oParam = ar\Params::getParam($aType, $sKey);
                $oParam->value = $sVal;
                $oParam->save();
            }
        }

        return $this->actionList();
    }

    protected function actionSettings()
    {
        $aValues = ['payment_method' => Api::getPaymentMethod()];
        $aItems = Api::getTitlePaymentMethod();

        $this->render(new Tool\Payments\view\Settings([
            'aValues' => $aValues,
            'aItems' => $aItems,
        ]));
    }

    protected function actionSaveSettings()
    {
        $sPaymentMethod = $this->getInDataVal(Api::PAYMENT_METHOD);
        SysVar::set(Api::PAYMENT_METHOD, $sPaymentMethod);

        return $this->actionList();
    }
}
