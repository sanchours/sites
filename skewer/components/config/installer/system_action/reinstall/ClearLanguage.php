<?php

namespace skewer\components\config\installer\system_action\reinstall;

use skewer\base\command\Action;
use skewer\components\i18n\models\LanguageValues;

class ClearLanguage extends Action
{
    public function init()
    {
    }

    public function execute()
    {
        LanguageValues::deleteAll([
            'override' => LanguageValues::overrideNo,
            'language' => LanguageValues::getSystemLanguage(),
        ]);

        \Yii::$app->getI18n()->clearCache();
    }

    public function rollback()
    {
    }
}
