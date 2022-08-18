<?php

namespace skewer\components\i18n\command\switch_language;

use skewer\base\section\Parameters;
use skewer\base\SysVar;
use skewer\components\i18n\Languages;

/**
 * Установка языка.
 */
class SwichLanguage extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        SysVar::set('language', $this->getNewLanguage());
        Languages::setActive($this->getNewLanguage(), 1);
        Languages::setActive($this->getOldLanguage(), 0);
        Parameters::setParams(\Yii::$app->sections->getValue('root', $this->getNewLanguage()), Parameters::settings, 'language', $this->getNewLanguage());
        \Yii::$app->language = $this->getNewLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        SysVar::set('language', $this->getOldLanguage());
        Languages::setActive($this->getNewLanguage(), 0);
        Languages::setActive($this->getOldLanguage(), 1);
        Parameters::setParams(\Yii::$app->sections->getValue('root', $this->getOldLanguage()), Parameters::settings, 'language', $this->getOldLanguage());
        \Yii::$app->language = $this->getOldLanguage();
    }
}
