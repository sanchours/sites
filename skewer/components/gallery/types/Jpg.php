<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.05.2017
 * Time: 14:37.
 */

namespace skewer\components\gallery\types;

use skewer\helpers\Image;

class Jpg extends \skewer\components\gallery\types\Prototype
{
    /**
     * @const int Качество, создаваемых jpg изображений
     */
    const default_quality = 90;

    /**
     * Качество создаваемого изображения. используется только для jpg;.
     *
     * @var int
     */
    protected $iCurrentQuality = self::default_quality;

    public function getCleanTpl($aParams)
    {
        $oImg = parent::getCleanTpl($aParams);

        $red = imagecolorallocate($oImg, Image::$aColor['r'], Image::$aColor['g'], Image::$aColor['b']);
        imagefilledrectangle($oImg, 0, 0, $aParams['iWidth'], $aParams['iHeight'], $red);

        return $oImg;
    }

    public function addTransparent($aParams, $rOut)
    {
        $transparent = imagecolorallocate($rOut, Image::$aColor['r'], Image::$aColor['g'], Image::$aColor['b']);
        imagefill($rOut, 0, 0, $transparent);

        return $rOut;
    }

    public function createImg($oImg, $sFileName)
    {
        imagejpeg($oImg, $sFileName, $this->iCurrentQuality);

        return $sFileName;
    }

    public function createGD($sFileName)
    {
        return imagecreatefromjpeg($sFileName);
    }

    public static function getNumType()
    {
        return '2';
    }
}
