<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\command\Action;
use skewer\base\command\Exception;
use skewer\components\i18n\models\Language;

/**
 * Прототип команды для установки языковой версии.
 */
abstract class Prototype extends Action
{
    /**
     * Событие создание раздела для языковой ветки.
     */
    const LANGUAGE_ROOT_CREATE = 'languageRootCreate';

    /**
     * @var Language Текущий язык
     */
    private $language;

    /**
     * @var int
     */
    private $iRootSection = 0;

    /**
     * @var Language Язык источника
     */
    private $sourceLanguage;

    public function __construct(Language $language, Language $sourceLanguage = null)
    {
        $this->language = $language;
        $this->sourceLanguage = $sourceLanguage;
    }

    /**
     * Инициализация
     * Добавление слушателей событий.
     */
    protected function init()
    {
        $this->listenTo(self::LANGUAGE_ROOT_CREATE, 'setRootSection');
    }

    /**
     * @param string $language
     * @param int $iRootSection
     */
    public function setRootSection($language, $iRootSection)
    {
        if ($language == $this->language->name) {
            $this->iRootSection = $iRootSection;
        }
    }

    /**
     * Главный раздел создаваемой языковой версии.
     *
     * @return int
     */
    public function getRootSection()
    {
        return $this->iRootSection;
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

    /**
     * Язык - источник.
     *
     * @throws Exception
     *
     * @return Language
     */
    public function getSourceLanguage()
    {
        if ($this->sourceLanguage === null) {
            throw new Exception('SourceLanguage is null');
        }

        return $this->sourceLanguage;
    }

    /**
     * Имя языка источника.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getSourceLanguageName()
    {
        if ($this->sourceLanguage === null) {
            throw new Exception('SourceLanguage is null');
        }

        return $this->sourceLanguage->name;
    }
}
