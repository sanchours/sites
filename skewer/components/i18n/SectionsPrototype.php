<?php

namespace skewer\components\i18n;

use skewer\base\section\Page;
use yii\base\Component;

/**
 * Прототип класса для работы с системными разделами.
 * Class SectionsPrototype.
 */
abstract class SectionsPrototype extends Component
{
    /**
     * Возвращает значение системного раздела.
     *
     * @param $sName
     * @param string $sLanguage, по умолчанию - текущий
     *
     * @return mixed
     */
    abstract public function getValue($sName, $sLanguage = '');

    /**
     * Возвращает значения системного раздела для всех языков.
     *
     * @param $sName
     *
     * @return array language => value
     */
    abstract public function getValues($sName);

    /**
     * Установка записи системного раздела.
     *
     * @param $sName
     * @param $sTitle
     * @param $iValue
     * @param $sLanguage
     *
     * @return bool|int
     */
    abstract public function setSection($sName, $sTitle, $iValue, $sLanguage);

    /**
     * Список системных разделов для текущего языка.
     *
     * @param $sLanguage
     *
     * @return array
     */
    abstract public function getListByLanguage($sLanguage);

    /**
     * Удаляет все значения для языка.
     *
     * @param $sLanguage
     */
    public function removeByLanguage($sLanguage)
    {
    }

    /**
     * Список ключей для значения.
     *
     * @param $iValue
     *
     * @return array 'name'
     */
    abstract public function getByValue($iValue);

    /**
     * Отдает id главной страницы (def 78).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function main($sLang = '')
    {
        return $this->getValue('main', $sLang);
    }

    /**
     * Отдает id корневого раздела (def 3).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function root($sLang = '')
    {
        return $this->getValue('root', $sLang);
    }

    /**
     * Отдает id корневого раздела языковой версии (def 3).
     *
     * @param string $lang
     * @return int
     */
    public function languageRoot($lang = '')
    {
        return $this->getValue(Page::LANG_ROOT, $lang);
    }

    /**
     * Отдает id раздела 404 ошибки (def 137).
     *
     * @return int
     */
    public function page404()
    {
        return $this->getValue('404');
    }

    /**
     * Отдает id основного шаблона сборки (def 7).
     *
     * @return int
     */
    public function tplNew()
    {
        return $this->getValue('tplNew');
    }

    /**
     * Отдает id раздела 'верхнее меню' (def 69).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function topMenu($sLang = '')
    {
        return $this->getValue('topMenu', $sLang);
    }

    /**
     * Отдает id раздела 'левое меню' (def 70).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function leftMenu($sLang = '')
    {
        return $this->getValue('leftMenu', $sLang);
    }

    /**
     * Отдает id раздела 'Служебные разделы' (def 120).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function tools($sLang = '')
    {
        return $this->getValue('tools', $sLang);
    }

    /**
     * Отдает id раздела 'Сервисное меню' (def 243).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function serviceMenu($sLang = '')
    {
        return $this->getValue('serviceMenu', $sLang);
    }

    /**
     * Отдает id раздела раздела авторизации (def 274).
     *
     * @return int
     */
    public function auth()
    {
        return $this->getValue('auth');
    }

    /**
     * Отдает id корневого раздела с шаблонами (def 2).
     *
     * @return int
     */
    public function templates()
    {
        return $this->getValue('templates');
    }

    /**
     * Отдает id корневого раздела с библиотеками (def 1).
     *
     * @return int
     */
    public function library()
    {
        return $this->getValue('library');
    }

    /**
     * Список разделов, запрещенных для показа.
     *
     * @return array
     */
    public function getDenySections()
    {
        return array_unique(array_merge(
            $this->getValues('root'),
            $this->getValues(Page::LANG_ROOT),
            $this->getValues('library'),
            $this->getValues('templates')
        ));
    }

    /**
     * Отдает id поискового раздела (def 175).
     *
     * @param string $sLang язык
     *
     * @return int
     */
    public function search($sLang = '')
    {
        return $this->getValue('search', $sLang);
    }
}
