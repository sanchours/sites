<?php

namespace skewer\components\i18n\command\delete_branch;

use skewer\base\command\Action;
use skewer\components\i18n\models\Language;

/**
 * Прототип команды для установки языковой версии.
 */
abstract class Prototype extends Action
{
    /**
     * @var Language Текущий язык
     */
    private $language;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Инициализация
     * Добавление слушателей событий.
     */
    protected function init()
    {
    }

    /**
     * Текущий язык.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Текущий язык.
     *
     * @return string
     */
    public function getLanguageName()
    {
        return $this->language->name;
    }
}
