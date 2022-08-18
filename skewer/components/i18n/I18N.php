<?php

namespace skewer\components\i18n;

/**
 * Компонент для интернационализации
 * Class i18n.
 *
 * @property Admin admin
 */
class I18N extends \yii\i18n\I18N
{
    /**
     * Кэш массива языков.
     *
     * @var models\Language[]
     */
    private $aLanguages = false;

    /** @var Admin объект для хранения админского функционала */
    private $adminObj;

    /**
     * Язык для отображения.
     *
     * @var string
     */
    private $translateLanguage = '';

    /**
     * Язык для отображения.
     *
     * @return string
     */
    public function getTranslateLanguage()
    {
        return ($this->translateLanguage) ? $this->translateLanguage : \Yii::$app->language;
    }

    /**
     * Язык для отображения.
     *
     * @param string $translateLanguage
     */
    public function setTranslateLanguage($translateLanguage)
    {
        $this->translateLanguage = $translateLanguage;
    }

    /**
     * Запросник админского набора функций.
     *
     * @return Admin
     */
    public function getAdmin()
    {
        if (!isset($this->adminObj)) {
            $this->adminObj = new Admin();
        }

        return $this->adminObj;
    }

    /**
     * Удаление кэшей для категории.
     *
     * @param $sCategory
     */
    public function clearCacheByCategory($sCategory)
    {
        $translate = $this->getMessageSource($sCategory);
        if ($translate instanceof MessageSource) {
            $translate->clearCache();
        }
    }

    /**
     * Удаление кэшей для категории.
     */
    public function clearCache()
    {
        $translate = $this->getMessageSource('*');
        if ($translate instanceof MessageSource) {
            $translate->clearCache();
        }
    }

    /**
     * Возвращает значение ключа на разных языках.
     *
     * @param $sCategory
     * @param $sName
     *
     * @return array
     */
    public function getValues($sCategory, $sName)
    {
        $aValues = [];

        foreach ($this->getLanguages() as $aLang) {
            $aValues[$aLang['name']] = \Yii::t($sCategory, $sName, [], $aLang['name']);
        }

        return $aValues;
    }

    /**
     * Выдает полный список языков.
     *
     * @return models\Language[]
     */
    protected function getLanguages()
    {
        if ($this->aLanguages === false) {
            $this->aLanguages = Languages::getAll();
        }

        return $this->aLanguages;
    }
}
