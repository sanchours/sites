<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.05.2017
 * Time: 14:37.
 */

namespace skewer\components\gallery\types;

class Gif extends \skewer\components\gallery\types\Prototype
{
    public function getCleanTpl($aParams)
    {
        $destination_resource = parent::getCleanTpl($aParams);

        $this->addTransparent($aParams, $destination_resource);

        return $destination_resource;
    }

    public function addTransparent($aParams, $rOut)
    {
        $rOut = $this->getCanvas($aParams['image'], $rOut);

        return $rOut;
    }

    public function createImg($oImg, $sFileName)
    {
        imagegif($oImg, $sFileName ? $sFileName : null);

        return $sFileName;
    }

    public function createGD($sFileName)
    {
        return imagecreatefromgif($sFileName);
    }

    public static function getNumType()
    {
        return '1';
    }

    public function getCanvas($oImg, $dstImg)
    {
        if (($iTransparent_source_index = imagecolortransparent($oImg)) !== -1) {
            $palletsize = imagecolorstotal($oImg);
            if ($iTransparent_source_index >= 0 && $iTransparent_source_index < $palletsize) {
                $aTransparent_color = imagecolorsforindex($oImg, $iTransparent_source_index);
                $iTransparent_destination_index = imagecolorallocate($dstImg, $aTransparent_color['red'], $aTransparent_color['green'], $aTransparent_color['blue']);
                imagecolortransparent($dstImg, $iTransparent_destination_index);
                imagefill($dstImg, 0, 0, $iTransparent_destination_index);
            } else {
                $iTransparent_destination_index = imagecolorallocate($dstImg, 255, 255, 255);
                imagefill($dstImg, 0, 0, $iTransparent_destination_index);
            }
        } else {
            $iTransparent_destination_index = imagecolorallocate($dstImg, 255, 255, 255);
            imagefill($dstImg, 0, 0, $iTransparent_destination_index);
        }

        return $dstImg;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($iWidth, $iHeight, $oImage)
    {
        $rImageBuffer = imagecreate($iWidth, $iHeight);

        return $this->getCanvas($oImage, $rImageBuffer);
    }
}
