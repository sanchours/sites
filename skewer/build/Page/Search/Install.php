<?php

declare(strict_types=1);

namespace skewer\build\Page\Search;

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
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function install()
    {
        if (!$this->_formService->hasFormWithSlug(SearchEntity::tableName())) {
            SearchEntity::createTable();
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
