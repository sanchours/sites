<?php

namespace skewer\build\Catalog\LeftList;

use skewer\build\Adm;

/**
 * Прототип класса для вкладок
 * Class ModulePrototype.
 */
class ModulePrototype extends Adm\Tree\ModulePrototype implements ModuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        \skewer\build\Tool\LeftList\ModulePrototype::updateLanguage();
        parent::init();
    }

    protected function checkAccess()
    {
    }
}
