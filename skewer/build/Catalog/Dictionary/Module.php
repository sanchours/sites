<?php

namespace skewer\build\Catalog\Dictionary;

use skewer\base\site_module\Context;
use skewer\build\Catalog\LeftList\ModuleInterface;

/**
 * Модуль для редактирования записей в каталоге
 * Class Module.
 */
class Module extends \skewer\build\Tool\Dictionary\Module implements ModuleInterface
{
    public function __construct(Context $oContext)
    {
        parent::__construct($oContext);
        $oContext->setModuleName('Dictionary');
        $oContext->setModuleWebDir('/skewer/build/Tool/Dictionary');
        $oContext->setModuleDir(RELEASEPATH . 'build/Tool/Dictionary');
    }

    /**
     * Модуль наследуется от аналогичного из панели управления
     * Перекрыт чтобы не срабатывали родительские проверки доступности.
     */
    protected function checkAccess()
    {
    }
}
