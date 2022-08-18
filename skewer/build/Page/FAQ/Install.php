<?php

namespace skewer\build\Page\FAQ;

use skewer\components\config\InstallPrototype;
use skewer\components\forms\service\FormService;

class Install extends InstallPrototype
{
    /** @var FormService $_formService */
    private $_formService;

    public function init()
    {
        $this->_formService = new FormService();

        return true;
    }

    // func

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function install()
    {
        if (!$this->_formService->hasFormWithSlug(FaqEntity::tableName())) {
            FaqEntity::createTable();
        }

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}//class
