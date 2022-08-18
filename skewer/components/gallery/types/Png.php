<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.05.2017
 * Time: 14:37.
 */

namespace skewer\components\gallery\types;

use skewer\helpers\Image;

class Png extends Jpg
{
    public function getCleanTpl($aParams)
    {
        $oImg = parent::getCleanTpl($aParams);

        $oImg = $this->addTransparent($aParams, $oImg);

        return $oImg;
    }

    public function addTransparent($aParams, $rOut)
    {
        imagealphablending($rOut, false);
        imagesavealpha($rOut, true);
        /*закрасим возможно прозрачным цветом*/
        $red = imagecolorallocatealpha($rOut, Image::$aColor['r'], Image::$aColor['g'], Image::$aColor['b'], 127);
        imagefill($rOut, 0, 0, $red);

        return $rOut;
    }

    public function createImg($oImg, $sFileName)
    {
        imagepng($oImg, $sFileName ? $sFileName : null);

        return $sFileName;
    }

    public function createGD($sFileName)
    {
        $oTmp = imagecreatefrompng($sFileName);

        // image in the form of black))
        imagealphablending($oTmp, false);

        // of transparency is preserved)
        imagesavealpha($oTmp, true);

        return $oTmp;
    }

    public static function getNumType()
    {
        return '3';
    }
}
