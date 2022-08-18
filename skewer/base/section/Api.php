<?php

namespace skewer\base\section;

use skewer\components\auth\Policy;

/**
 * Класс для работы с разделами
 * Class Api.
 */
class Api
{
    /**
     * Список вложенных разделов в формате alias=>id.
     *
     * @var array
     */
    private static $aAliasSubList = [];

    /**
     * Разделитель пути.
     *
     * @var string
     */
    public static $sDelimiter = '/';

    /**
     * По псевдониму и базовому разделу.
     *
     * @param $sAlias
     * @param int $iBaseId
     *
     * @return int
     */
    public static function getIdByAlias($sAlias, $iBaseId = 0)
    {
        $sAlias = mb_strtolower(trim($sAlias), 'utf-8');

        if (!isset(static::$aAliasSubList[$iBaseId])) {
            static::$aAliasSubList[$iBaseId] = static::getAliasSubList($iBaseId);
        }

        return (isset(static::$aAliasSubList[$iBaseId][$sAlias])) ? static::$aAliasSubList[$iBaseId][$sAlias] : 0;
    }

    /**
     * Для каждого раздела из массива $aSections строит цепочку titlePath -> id
     * Пример:.
     * array(
     * 'группа товаров 1' => 293
     * 'группа товаров 1/список товаров 1' => 282
     * 'группа товаров 1/список товаров 2' => 283
     * ).
     *
     * @param array $aSections - разделы
     * @param string $sParentTitle - название род.раздела
     *
     * @return array
     */
    private static function chainTitle($aSections, $sParentTitle = '')
    {
        $out = [];

        foreach ($aSections as $aSection) {
            $currentLabel = ($sParentTitle)
                ? $sParentTitle . $aSection['title'] . self::$sDelimiter
                : $aSection['title'] . self::$sDelimiter;

            $out[mb_strtolower(trim($currentLabel, ' ' . self::$sDelimiter), 'utf-8')] = $aSection['id'];
            if (!empty($aSection['children'])) {
                $out = array_merge($out, self::chainTitle($aSection['children'], $currentLabel));
            }
        }

        return $out;
    }

    /**
     * Список вложенных разделов в формате alias=>id.
     *
     * @param $id
     *
     * @return array
     */
    public static function getAliasSubList($id)
    {
        //все разделы
        $fullSections = Policy::getAvailableSections();

        //упорядочиваем в дерево
        $aTree = Tree::collect($id, $fullSections, 0, true);

        //строим цепочки titlePath -> id
        return self::chainTitle($aTree);
    }

    /**
     * Добавление раздела.
     *
     * @param $iParent
     * @param $alias
     * @param $iTemplate
     *
     * @return bool|int
     */
    public static function addSection($iParent, $alias, $iTemplate)
    {
//        $oTree = new \Tree();
//
//        $iSection = $oTree->addSection( $iParent, $alias, $iTemplate );

        $oSection = Tree::addSection($iParent, $alias, $iTemplate);

        if ($oSection) {
            $alias = mb_strtolower(trim($alias), 'utf-8');

            /* Добавляем во внутренние массивы */
            if (isset(static::$aAliasSubList[$iParent])) {
                static::$aAliasSubList[$iParent][$alias] = $oSection->id;
            }

            foreach (static::$aAliasSubList as &$aAliasList) {
                foreach ($aAliasList as $sAlias => $iKey) {
                    if ($iKey == $iParent) {
                        $aAliasList[$sAlias . self::$sDelimiter . $alias] = $oSection->id;
                    }
                }
            }
        }

        return $oSection->id;
    }
}
