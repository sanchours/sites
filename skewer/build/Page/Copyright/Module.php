<?php

namespace skewer\build\Page\Copyright;

use skewer\base\site_module;

/**
 * Модуль антикопипаста
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    /**
     * @const Группа параметров модуля
     */
    const GROUP_COPYRIGHT = 'Copyright';

    public $template = 'copyright.php';

    public function init()
    {
        $this->setParser(parserPHP);

        return true;
    }

    public function autoInitAsset()
    {
        return false;
    }

    public function execute()
    {
        $this->setTemplate($this->template);

        return psComplete;
    }

    /** {@inheritdoc} */
    public function canHaveContent()
    {
        return false;
    }
}
