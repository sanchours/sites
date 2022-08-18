<?php

namespace skewer\base\ft;

/**
 * Набор функций для работы с языковыми версиями
 * var 0.80.
 *
 * $Author$
 * $Revision$
 * $Date$
 */
class Lang
{
    const delimiter = '|';

    /**
     * инициализация языковой переменной.
     *
     * @static
     *
     * @return bool
     */
    public static function init()
    {
        global $aLangList;
        if (!Fnc::hasLanguages()) {
            return false;
        }
        global $ft_language;
        if (isset($ft_language)) {
            return true;
        }

        // задание параметров, если их нет
        foreach ($aLangList as $sKey => $sVal) {
            if (!isset($aLangList[$sKey]['alias'])) {
                $aLangList[$sKey]['alias'] = mb_substr($aLangList[$sKey]['name'], 0, 1);
            }
        }

        $ft_language = [
            'default' => $aLangList[$aLangList['default']]['alias'],
            'now' => $aLangList[$aLangList['default']]['alias'],
            'list' => [],
        ];

        $ft_language['now'] = 'ru';

        foreach ($aLangList as $key => $value) {
            if ($key !== 'default') {
                $ft_language['list'][$value['alias']] = [
                    'alias' => $value['alias'],
                    'title' => $value['locale_name'],
                ];
            }
        }

        return true;
    }

    /**
     * текущий язык.
     *
     * @static
     *
     * @return mixed
     */
    public static function lang()
    {
        global $ft_language;

        return $ft_language['now'];
    }

    /**
     * язык по умолчанию.
     *
     * @static
     *
     * @return mixed
     */
    public static function defaultLang()
    {
        global $ft_language;

        return $ft_language['default'];
    }

    /**
     * текущий язык не является языком по умолчанию.
     *
     * @static
     *
     * @return bool|mixed
     */
    public static function notDefaultLang()
    {
        $lang = self::lang();

        return $lang === self::defaultLang() ? false : $lang;
    }

    /**
     * набор языков.
     *
     * @static
     *
     * @param bool $num_index
     *
     * @return array
     */
    public static function langList($num_index = false)
    {
        global $ft_language;
        if (!$ft_language) {
            return [];
        }

        return $num_index ? array_values($ft_language['list']) : $ft_language['list'];
    }

    /**
     * набор языков без языка по умолчанию.
     *
     * @static
     *
     * @return array
     */
    public static function notDefaultLangList()
    {
        $list = self::langList();
        unset($list[self::defaultLang()]);

        return $list;
    }

    /**
     * набор языков с числовыми индексами.
     *
     * @static
     *
     * @param bool $num_index
     *
     * @return array
     */
    public static function listInt($num_index = false)
    {
        return self::langList(1);
    }

    /**
     * имеет ли набор такой язык.
     *
     * @static
     *
     * @param $lang
     *
     * @return bool
     */
    public static function hasLang($lang)
    {
        global $ft_language;

        return isset($ft_language['list'][$lang]);
    }

    /**
     * Отдает объект языковой версии.
     *
     * @static
     *
     * @param Model $oModel
     *
     * @return Entity
     */
    public static function getLangEntity(Model $oModel)
    {
        // создаем новую таблицу с языковым расширением
        $oLangEntity = Entity::get($oModel->getName() . '_lang', $oModel->getTitle() . '(lang)')
            ->clear()

            ->addField('_id', 'int(11)')
            ->addField('_lang', 'varchar(7)')

            ->selectFields('_id,_lang')
            ->addIndex();

        // добавление мульиязычных полей
        foreach ($oModel->getFileds() as $oField) {
            if ($oField->isMultilang()) {
                $oLangEntity->addFieldObject($oField);
            }
        }

        return $oLangEntity->dropMultilang(true);
    }
}

Lang::init();
