<?php

namespace skewer\build\Tool\Order;

use skewer\base\site_module\Context;
use skewer\build\Adm;
use skewer\build\Tool;

/**
 * Проекция редактора баннеров для слайдера в панель управления
 * Class Module.
 */
class Module extends Adm\Order\Module implements Tool\LeftList\ModuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        Tool\LeftList\ModulePrototype::updateLanguage();
        parent::init();
    }

    public function __construct(Context $oContext)
    {
        parent::__construct($oContext);
        //$oContext->setTplDirectory('/skewer/build/Adm/Order/templates');
        $oContext->setModuleWebDir('/skewer/build/Adm/Order');
        $oContext->setModuleDir(RELEASEPATH . 'build/Adm/Order');

        $oContext->setModuleLayer('Adm');
    }

    public function getName()
    {
        return $this->getModuleName();
    }
}
