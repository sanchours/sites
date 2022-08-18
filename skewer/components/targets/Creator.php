<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 06.06.2016
 * Time: 16:28.
 */

namespace skewer\components\targets;

class Creator
{
    /**
     * Для хранения оюъекта цели.
     *
     * @var null
     */
    private static $oType = null;

    /**
     * Отдает объект цели.
     *
     * @param $sType
     * @param mixed $bOverlay
     */
    public static function getObject($sType, $bOverlay = false)
    {
        if (($bOverlay) or (self::$oType === null)) {
            self::setObject($sType);
        }

        return self::$oType;
    }

    /**
     * Создает объект цели.
     *
     * @param $sType
     */
    private static function setObject($sType)
    {
        self::$oType = null;
        $sClassName = 'skewer\components\targets\types' . '\\' . ucfirst($sType);
        self::$oType = new $sClassName();
    }

    /**
     * Собирает массив доступных типов целей.
     *
     * @return array
     */
    public static function getTypes()
    {
        $aDeny = ['.', '..', 'Prototype.php'];

        $aTypes = [];

        $aTempTypes = scandir(__DIR__ . '/types');

        foreach ($aTempTypes as $item) {
            if (array_search($item, $aDeny) === false) {
                $aTypes[] = str_replace('.php', '', $item);
            }
        }

        return $aTypes;
    }

    public static function getParams()
    {
        $aTypes = self::getTypes();

        $aOut = [];

        foreach ($aTypes as $type) {
            $oType = self::getObject($type, true);

            $aParams = $oType->getParams();

            foreach ($aParams as $item) {
                $aOut[] = $item;
            }
        }

        return $aOut;
    }

    public static function checkInForms($sName)
    {
        $aTypes = self::getTypes();

        $aOut = [];

        foreach ($aTypes as $type) {
            $oType = self::getObject($type, true);

            $aForms = $oType->checkTarget($sName);

            foreach ($aForms as $item) {
                $aOut[] = $item;
            }
        }

        return $aOut;
    }

    public static function setParams($aData)
    {
        $aTypes = self::getTypes();

        $aOut = [];

        foreach ($aTypes as $type) {
            $oType = self::getObject($type, true);
            $oType->setParams($aData);
        }

        return $aOut;
    }
}
