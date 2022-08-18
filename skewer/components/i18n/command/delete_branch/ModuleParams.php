<?php

namespace skewer\components\i18n\command\delete_branch;

use skewer\components\i18n\models\Params;

/**
 * Копирование данных модулей.
 */
class ModuleParams extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        Params::deleteAll(['language' => $this->getLanguageName()]);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
