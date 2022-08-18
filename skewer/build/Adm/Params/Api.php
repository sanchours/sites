<?php

namespace skewer\build\Adm\Params;

use skewer\base\section\Parameters;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.14
 * Time: 12:55.
 */
class Api
{
    /**
     * Фильтруем параметры.
     *
     * @param $aItemList array массив на вход
     * @param $sFilter string строка для фильтра
     * @param int $iSectionId
     *
     * @return array
     */
    public static function filterParams($aItemList, $sFilter, $iSectionId = 0)
    {
        $aResult = [];

        // для текста, начинающегося с точки - фильтр по группе
        if (mb_strpos($sFilter, '.') === 0) {
            // отрезаем точку в начале
            $sFilter = mb_substr($sFilter, 1);

            // ищем полное соответствие группе
            foreach ($aItemList as $aItem) {
                if ($aItem['group'] == $sFilter) {
                    $aResult[] = $aItem;
                }
            }

            // если не нашли - любое вхождение
            if (!$aResult) {
                foreach ($aItemList as $aItem) {
                    if (mb_stripos($aItem['group'], $sFilter) !== false) {
                        $aResult[] = $aItem;
                    }
                }
            }
        } elseif ($sFilter === ':own') {
            foreach ($aItemList as $aItem) {
                if ($aItem['parent'] == $iSectionId) {
                    $aResult[] = $aItem;
                }
            }
        }

        // для остальных - общий поиск
        else {
            foreach ($aItemList as $aItem) {
                $bFlag = false;

                // перебираем все поля
                foreach ($aItem as $aValue) {
                    // ищем подстроку в каждом параметре
                    if (is_string($aValue) || is_numeric($aValue)) {
                        if (mb_stripos($aValue, $sFilter) !== false) {
                            $bFlag = true;
                        }
                    }
                }

                if ($bFlag) {
                    $aResult[] = $aItem;
                }
            }
        }

        return $aResult;
    }

    /**
     * Отдает все доступные для раздела группы.
     *
     * @param $iSectionId
     *
     * @return array
     */
    public static function getAllGroups($iSectionId)
    {
        $aParams = Parameters::getList($iSectionId)->groups()->rec()->asArray()->get();

        $aParams = array_keys($aParams);

        asort($aParams);

        return array_combine($aParams, $aParams);
    }

    // дополнительный набор параметров
    protected static function getAddParamList()
    {
        $aHidden = [
            'view' => 'hide',
            'listColumns' => [
                'hidden' => true,
            ],
        ];

        return [
            'id' => $aHidden,
            'parent' => $aHidden,
            'name' => ['listColumns' => ['width' => 100]],
            'title' => ['listColumns' => ['width' => 200]],
            'value' => ['listColumns' => ['width' => 200]],
        ];
    }
}
