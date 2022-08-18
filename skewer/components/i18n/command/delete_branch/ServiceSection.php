<?php

namespace skewer\components\i18n\command\delete_branch;

/**
 * Копирование сервисных разделов
 * Основная часть будет скопирована при копировании разделов. Здесь будет только то, что не создает новый раздел, например, root (3).
 */
class ServiceSection extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        \Yii::$app->sections->removeByLanguage($this->getLanguageName());
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
