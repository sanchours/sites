<?php

namespace skewer\components\gallery\types;

/**
 * Class Webp
 * @package skewer\components\gallery\types
 */
class Webp extends Jpg
{
    const TYPE = 18;

    /**
     * @param $oImg
     * @param $sFileName
     * @return mixed
     */
    public function createImg($oImg, $sFileName)
    {
        imagewebp($oImg, $sFileName ? $sFileName : null);
        return $sFileName;
    }

    /**
     * @param $sFileName
     * @return false|\GdImage|resource
     */
    public function createGD($sFileName)
    {
        $oTmp = imagecreatefromwebp($sFileName);
        // image in the form of black))
        imagealphablending($oTmp, false);
        // of transparency is preserved)
        imagesavealpha($oTmp, true);
        return $oTmp;
    }

    /**
     * @return int
     */
    public static function getNumType()
    {
        return self::TYPE;
    }
}
