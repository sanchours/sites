<?php

namespace skewer\build\Page\Documents;

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
        if (!$this->_formService->hasFormWithSlug(ReviewEntity::tableName())) {
            ReviewEntity::createTable();
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
