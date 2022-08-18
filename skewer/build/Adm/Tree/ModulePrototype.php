<?php

namespace skewer\build\Adm\Tree;

use skewer\base\site_module;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;
use yii\base\UserException;

/**
 * Родительский класс для модулей относящихся к дереву разделов.
 */
class ModulePrototype extends Cms\Tabs\ModulePrototype implements site_module\SectionModuleInterface
{
    /** @var int id раздела */
    protected $sectionId = 0;

    public function sectionId()
    {
        return $this->sectionId;
    }

    /**
     * проверка прав доступа.
     */
    protected function checkAccess()
    {
        // проверить права доступа
        if (!CurrentAdmin::canRead($this->sectionId())) {
            throw new UserException('accessDenied');
        }
    }
}
