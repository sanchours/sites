<?php

namespace skewer\build\Cms\Lang;

use skewer\build\Cms;
use skewer\components\i18n\Languages;
use yii\base\UserException;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /**
     * Выбор интерфейса для отображения в текущей ситуации.
     *
     * @return bool
     */
    protected function actionInit()
    {
        $aLanguages = Languages::getAdminActive();

        if (count($aLanguages) < 2) {
            return psRendered;
        }

        $this->addInitParam('currentLang', \Yii::$app->i18n->getTranslateLanguage());
        $this->addInitParam('langList', $aLanguages);

        return psComplete;
    }

    protected function actionSetLang()
    {
        $sLangName = $this->get('lang', '');
        $oLang = Languages::getByName($sLangName);

        if (!$oLang) {
            throw new UserException("Язык [{$sLangName}] не найден");
        }
        if (!$oLang->admin) {
            throw new UserException("Язык [{$sLangName}] не может быть выбран (не активирован)");
        }
        \Yii::$app->i18n->admin->setLang($sLangName);

        $this->fireJSEvent('reload');
    }
}
