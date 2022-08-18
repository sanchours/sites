<?php

namespace skewer\components\i18n\command\add_branch;

/**
 * Активация языка.
 */
class ActivateLanguage extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->getLanguage()->active = 1;
        $this->getLanguage()->save();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->getLanguage()->active = 0;
        $this->getLanguage()->save();
    }
}
