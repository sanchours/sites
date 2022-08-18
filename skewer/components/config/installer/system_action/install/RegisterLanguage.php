<?php
/**
 * @class RegisterLanguage
 *
 * @author ArmiT
 * @date 24.01.14
 * @project canape
 */

namespace skewer\components\config\installer\system_action\install;

use skewer\components\config\installer;
use skewer\components\i18n\Categories;

class RegisterLanguage extends installer\Action
{
    public function init()
    {
    }

    public function execute()
    {
        Categories::updateModuleLanguageValues($this->module);
    }

    public function rollback()
    {
    }
}
