<?php

namespace skewer\build\Tool\UnderConstruction;

use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;
use skewer\components\i18n\Messages;
use skewer\components\i18n\models\LanguageValues;

class Module extends Tool\LeftList\ModulePrototype
{
    protected function preExecute()
    {
        if (!CurrentAdmin::isSystemMode()) {
            $this->addMessage(\Yii::t('uconst', 'msg_notif_title'), \Yii::t('uconst', 'msg_notif_desc'));
        }
    }

    protected function actionInit()
    {
        $aIndex['show'] = Api::getInstallUC();
        $aIndex['template'] = Api::getDataBlock();

        $this->render(new Tool\UnderConstruction\view\Index([
            'aIndex' => $aIndex,
        ]));
    }

    protected function actionSave()
    {
        $bShow = $this->getInDataVal('show');
        Api::setInstallUC($bShow);

        $sTemplate = $this->getInDataVal('template');
        $sCategory = 'uconst';
        $sMessage = 'under_construction';
        $sLanguage = \Yii::$app->language;
        $oRow = Messages::getByName($sCategory, $sMessage, $sLanguage);

        if (!$oRow) {
            $oRow = new LanguageValues();
            $oRow->category = $sCategory;
            $oRow->message = $sMessage;
            $oRow->language = $sLanguage;
            $oRow->data = 1;
        }
        $oRow->value = $sTemplate;
        $oRow->override = ($oRow->override || $oRow->isAttributeChanged('value'))
            ? LanguageValues::overrideYes
            : LanguageValues::overrideNo;

        if ($oRow->save()) {
            $this->addMessage(\Yii::t('uconst', 'msg_notif_title'), \Yii::t('uconst', 'msg_save_desc'));
        }

        $this->actionInit();
    }
}
