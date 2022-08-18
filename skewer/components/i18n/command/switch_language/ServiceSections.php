<?php

namespace skewer\components\i18n\command\switch_language;

/**
 * Перепись языка для системных разделов.
 */
class ServiceSections extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        \skewer\components\i18n\models\ServiceSections::updateAll(['language' => $this->getNewLanguage()], ['language' => $this->getOldLanguage()]);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        \skewer\components\i18n\models\ServiceSections::updateAll(['language' => $this->getOldLanguage()], ['language' => $this->getNewLanguage()]);
    }
}
