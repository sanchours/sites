<?php

namespace skewer\build\Tool\Crm;

use skewer\base\SysVar;
use skewer\build\Tool;
use skewer\build\Tool\Crm\models\DealEvent;
use skewer\build\Tool\Crm\models\DealType;
use skewer\build\Tool\Crm\view\DealEventEdit;
use skewer\build\Tool\Crm\view\DealEventList;
use skewer\build\Tool\Crm\view\DealTypeEdit;
use skewer\build\Tool\Crm\view\DealTypeList;
use skewer\build\Tool\Crm\view\Index;
use yii\helpers\ArrayHelper;

class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $values['token_email'] = SysVar::get(Api::CRM_SYSVAR_TOKEN_EMAIL);
        $values['email'] = SysVar::get(Api::CRM_SYSVAR_EMAIL);
        $values['token'] = SysVar::get(Api::CRM_SYSVAR_TOKEN);
        $values['domain'] = SysVar::get(Api::CRM_SYSVAR_DOMAIN);
        $values['integration'] = SysVar::get(Api::CRM_SYSVAR_INTEGRATION, Api::CRM_EMAIL_INTEGRATION);

        $this->render(new Index([
            'sToken' => \Yii::t('crm', 'token'),
            'sDomain' => \Yii::t('crm', 'domain'),
            'sTokenEmail' => \Yii::t('crm', 'token_email'),
            'sMail' => \Yii::t('crm', 'email'),
            'sIntegration' => \Yii::t('crm', 'integration'),
            'aValues' => $values,
        ]));

        return psComplete;
    }

    protected function actionSave()
    {
        try {
            $aData = $this->getInData();

            if ($aData['integration'] == Api::CRM_API_INTEGRATION) {
                $oldToken = SysVar::get(Api::CRM_SYSVAR_TOKEN);
                $newToken = ArrayHelper::getValue($aData, 'token', '');
                $oldDomain = SysVar::get(Api::CRM_SYSVAR_DOMAIN);
                $newDomain = ArrayHelper::getValue($aData, 'domain', '');

                SysVar::set(Api::CRM_SYSVAR_TOKEN, $newToken);
                SysVar::set(Api::CRM_SYSVAR_DOMAIN, $newDomain);

                if (($oldDomain != $newDomain) && ($oldToken != $newToken)) {
                    DealType::checkList();
                    DealEvent::checkList();
                }
            } elseif ($aData['integration'] == Api::CRM_EMAIL_INTEGRATION) {
                SysVar::set(Api::CRM_SYSVAR_EMAIL, ArrayHelper::getValue($aData, 'email', ''));
                SysVar::set(Api::CRM_SYSVAR_TOKEN_EMAIL, ArrayHelper::getValue($aData, 'token_email', ''));
            }

            SysVar::set(Api::CRM_SYSVAR_INTEGRATION, ArrayHelper::getValue($aData, 'integration', ''));

            $this->actionInit();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    protected function actionSaveIntegration()
    {
        $aData = $this->get('formData', []);
        SysVar::set(Api::CRM_SYSVAR_INTEGRATION, ArrayHelper::getValue($aData, 'integration', ''));
        $this->actionInit();
    }

    protected function actionDealTypeList()
    {
        DealType::checkList();
        $this->render(new DealTypeList(['aFields' => DealType::getDealTypesList()]));

        return psComplete;
    }

    protected function actionDealTypeEdit()
    {
        $aData = $this->getInData();
        $oDealType = DealType::findOne(['id' => $aData['id']]);

        $this->render(new DealTypeEdit(['oDealType' => $oDealType]));

        return psComplete;
    }

    protected function actionSaveDealType()
    {
        $aData = $this->getInData();

        $oDealType = DealType::findOne(['id' => $aData['id']]);
        $oDealType->setAttributes($aData);
        $oDealType->save();

        return $this->actionDealTypeList();
    }

    protected function actionDealEventList()
    {
        DealEvent::checkList();

        $this->render(new DealEventList(['aFields' => DealEvent::getDealEventsList()]));

        return psComplete;
    }

    protected function actionDealEventEdit()
    {
        $aData = $this->getInData();
        $oDealEvent = DealEvent::findOne(['id' => $aData['id']]);

        $this->render(new DealEventEdit(['oDealEvent' => $oDealEvent]));

        return psComplete;
    }

    protected function actionSaveDealEvent()
    {
        $aData = $this->getInData();

        $oDealEvent = DealEvent::findOne(['id' => $aData['id']]);
        $oDealEvent->setAttributes($aData);
        $oDealEvent->save();

        return $this->actionDealEventList();
    }
}
