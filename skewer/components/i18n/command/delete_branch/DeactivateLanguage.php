<?php

namespace skewer\components\i18n\command\delete_branch;

/**
 * Деактивация языка.
 */
class DeactivateLanguage extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->getLanguage()->active = 0;
        $this->getLanguage()->save();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->getLanguage()->active = 1;
        $this->getLanguage()->save();
    }
}
