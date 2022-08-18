<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 20.09.2016
 * Time: 9:30.
 */

namespace skewer\components\content_generator;

class Api
{
    public static $sTplPath = '/templates/';

    public static function getDir()
    {
        return __DIR__ . self::$sTplPath;
    }

    /**
     * Формирует view со всему шаблонами.
     */
    public static function getAll()
    {
        $aGroups = Config::getGroups();

        $aConfig = Config::getItems();

        foreach ($aConfig['templates'] as $item) {
            if (isset($aGroups[$item['group']])) {
                $aGroups[$item['group']]['items'][] = $item;
            }
        }

        return ['groups' => $aGroups];
    }

    /**
     * Отдает шаблон по имени.
     *
     * @param $sName
     *
     * @return array
     */
    public static function getOne($sName)
    {
        $aConfig = Config::getItems();

        if (isset($aConfig['templates'][$sName])) {
            self::$sTplPath .= 'paths/' . $aConfig['templates'][$sName]['template'];
        } else {
            self::$sTplPath .= 'paths/none.php';
        }

        return [];
    }
}
