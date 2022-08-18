<?php

namespace skewer\build\Tool\AuthSocialNetwork;

use skewer\base\SysVar;
use skewer\build\Tool\AuthSocialNetwork\view\Index;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\libs\ulogin\Api;
use yii\helpers\ArrayHelper;

class Module extends ModulePrototype
{
    protected function actionInit()
    {
        $this->render(new Index([
            'values' => [
                'authSocialNetwork' => SysVar::get(Api::$nameAuthSocialNetwork),
                'typeDisplay' => SysVar::get(Api::$nameTypeDisplay),
                'typeTheme' => SysVar::get(Api::$nameTypeTheme),
            ],
        ]));
    }

    protected function actionSave()
    {
        SysVar::set(Api::$nameAuthSocialNetwork, $this->getInDataVal('authSocialNetwork'));
        SysVar::set(Api::$nameTypeDisplay, $this->getInDataVal('typeDisplay'));
        SysVar::set(Api::$nameTypeTheme, $this->getInDataVal('typeTheme'));

        $this->actionInit();
    }

    protected function actionUpdFields()
    {
        $aFormData = $this->get('formData', []);
        $authSocialNetwork = ArrayHelper::getValue($aFormData, 'authSocialNetwork');
        if (!$authSocialNetwork) {
            $this->addWarning(
                \Yii::t('socialNetwork', 'message_disable_auth_title'),
                \Yii::t('socialNetwork', 'message_disable_auth')
            );
        }
    }
}
