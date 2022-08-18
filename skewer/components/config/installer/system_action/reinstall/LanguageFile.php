<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.04.14
 * Time: 16:41.
 */

namespace skewer\components\config\installer\system_action\reinstall;

use skewer\base\command;
use skewer\components\config\installer\Module;
use skewer\components\i18n\Categories;

/**
 * Преустанавливает языковые значения для заданного модуля из файла
 * Class LanguageFile.
 */
class LanguageFile extends command\Action
{
    protected $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    protected function init()
    {
    }

    public function execute()
    {
        Categories::updateByCategory($this->module->languageCategory, $this->module->languageFile);
        Categories::updateByCategory($this->module->languageCategory, $this->module->presetDataFile, true);
    }

    public function rollback()
    {
    }
}
