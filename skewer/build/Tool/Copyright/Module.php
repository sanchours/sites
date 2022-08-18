<?php

namespace skewer\build\Tool\Copyright;

use skewer\build\Tool;
use skewer\components\i18n\models\LanguageValues;

/**
 * Модуль антикопипаста
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $this->stateEditModule();
    }

    /** Состояние: редактирование модуля */
    protected function stateEditModule()
    {
        $this->render(new Tool\Copyright\view\EditModule([
            'sFieldActivityTitle' => \Yii::t($this->languageCategory, 'field_activity'),
            'sFieldDisableInTitle' => \Yii::t($this->languageCategory, 'field_disable_in'),
            'aAllSections' => Api::getAllSections(),
            'aSectionsWithDisableCopyrightModule' => Api::getSectionsWithDisabledCopyrightModule(),
            'sFieldTextTitle' => \Yii::t($this->languageCategory, 'field_text'),
            'sActivityModule' => Api::getActivityModule(),
            'sTemplatedText' => \Yii::t($this->languageCategory, 'templateText'),
        ]));
    }

    /** Действие: cохранение настройки модуля */
    protected function actionSave()
    {
        $aData = $this->get('data');

        Api::setActivityModule($aData['activity']);
        Api::setSectionsWithDisabledCopyrightModule($aData['disabledSection']);

        if (!$oLabel = LanguageValues::findOne(['message' => 'templateText', 'language' => \Yii::$app->i18n->getTranslateLanguage()])) {
            $oLabel = new LanguageValues();
        }

        $oLabel->message = 'templateText';
        $oLabel->value = $aData['text'];
        $oLabel->language = \Yii::$app->i18n->getTranslateLanguage();
        $oLabel->category = $this->languageCategory;
        $oLabel->status = LanguageValues::statusTranslated;
        $oLabel->override = LanguageValues::overrideYes;
        $oLabel->data = 0;
        $oLabel->save();

        $this->actionInit();
    }
}
