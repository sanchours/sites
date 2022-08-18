<?php

namespace skewer\components\i18n\command\switch_language;

use skewer\base\command\Action;

/**
 * Прототип команды для смены языка.
 */
abstract class Prototype extends Action
{
    /**
     * @var string Новый язык
     */
    private $newLanguage = '';

    /**
     * @var string Новый язык
     */
    private $oldLanguage = '';

    /**
     * @param $oldLanguage
     * @param $newLanguage
     */
    public function __construct($oldLanguage, $newLanguage)
    {
        $this->oldLanguage = $oldLanguage;
        $this->newLanguage = $newLanguage;
    }

    /**
     * Инициализация
     * Добавление слушателей событий.
     */
    protected function init()
    {
    }

    /**
     * Новый язык.
     *
     * @return string
     */
    public function getNewLanguage()
    {
        return $this->newLanguage;
    }

    /**
     * Старый язык.
     *
     * @return string
     */
    public function getOldLanguage()
    {
        return $this->oldLanguage;
    }
}
