<?php

namespace skewer\components\i18n\command\add_branch;

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
        $aSections[$this->getSourceLanguageName()] = \Yii::$app->sections->getListByLanguage($this->getSourceLanguageName());
        $aSections[$this->getLanguageName()] = \Yii::$app->sections->getListByLanguage($this->getLanguageName());

        foreach ($aSections[$this->getSourceLanguageName()] as $name => $value) {
            if (!isset($aSections[$this->getLanguageName()][$name]) && isset($aSections[$this->getSourceLanguageName()][$name])) {
                $val = $aSections[$this->getSourceLanguageName()][$name];

                $sTitle = \Yii::t('app', $name, [], $this->getLanguageName());

                \Yii::$app->sections->setSection($name, $sTitle, $val, $this->getLanguageName());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        \Yii::$app->sections->removeByLanguage($this->getLanguageName());
    }
}
