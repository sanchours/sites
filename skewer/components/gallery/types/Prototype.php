<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.05.2017
 * Time: 14:35.
 */

namespace skewer\components\gallery\types;

use skewer\components\gallery\Config;

abstract class Prototype
{
    /**
     * Отдает чистое изображение.
     *
     * @param $aParams
     *
     * @return resource
     */
    public function getCleanTpl($aParams)
    {
        if ($aParams['custom']) {
            $destination_resource = imagecreatetruecolor(round($aParams['iSourceWidth'] - $aParams['iLeftDelayOnImg']), round($aParams['iSourceHeight'] - $aParams['iTopDelayOnImg']));
        } else {
            $destination_resource = imagecreatetruecolor($aParams['iWidth'], $aParams['iHeight']);
        }

        return $destination_resource;
    }

    abstract public function createImg($oImg, $sFileName);

    abstract public function createGD($sFileName);

    public function applyWaterMark($oImage, $aParams)
    {
        if (!is_file($aParams['sPossibleFileName'])) {
            $sFontFile = BUILDPATH . 'common/fonts/' . $aParams['sFont'];

            $iWMWidth = imagesx($oImage);
            $iWMHeight = imagesy($oImage);
            //$iAngle  = -rad2deg(atan2((-$iHeight), ($iWidth)));
            $iAngle = 0;
            $iTextColor = imagecolorallocatealpha($oImage, $aParams['aWatermarkColor']['red'], $aParams['aWatermarkColor']['green'], $aParams['aWatermarkColor']['blue'], $aParams['iAlphaLevel']);

            //$fSize = (($iWidth + $iHeight) / 2) * 2 / strlen($sWatermark);
            $fSize = 18;
            $aBox = imagettfbbox($fSize, $iAngle, $sFontFile, $aParams['sWatermark']);

            switch ($aParams['iAlign']) {
                case Config::alignWatermarkTopLeft:

                    $iX = $aParams['iMargin'];
                    $iY = $fSize + $aParams['iMargin'];

                    break;
                case Config::alignWatermarkTopRight:

                    $iX = $iWMWidth - abs($aBox[4] - $aBox[0]) - $aParams['iMargin'];
                    $iY = $fSize + $aParams['iMargin'];

                    break;
                case Config::alignWatermarkBottomLeft:

                    $iX = $aParams['iMargin'];
                    $iY = $iWMHeight - $fSize;

                    break;
                case Config::alignWatermarkBottomRight:

                    $iX = $iWMWidth - abs($aBox[4] - $aBox[0]) - $aParams['iMargin'];
                    $iY = $iWMHeight - $fSize;

                    break;
                case Config::alignWatermarkCenter:

                    $iX = $iWMWidth / 2 - abs($aBox[4] - $aBox[0]) / 2;
                    $iY = $iWMHeight / 2 + abs($aBox[5] - $aBox[1]) / 2;

                    break;

                default:

                    $iX = 0;
                    $iY = $fSize + $aParams['iMargin'];

                    break;
            }// type of align

            imagealphablending($oImage, true);
            imagettftext($oImage, $fSize, $iAngle, $iX, $iY, $iTextColor, $sFontFile, $aParams['sWatermark']);
            imagealphablending($oImage, false);
        } else {
            $rWatermarkImage = imagecreatefrompng($aParams['sPossibleFileName']);

            switch ($aParams['iAlign']) {
                case Config::alignWatermarkTopLeft:

                    $iX = $aParams['iMargin'];
                    $iY = $aParams['iMargin'];

                    break;
                case Config::alignWatermarkTopRight:

                    $iX = $aParams['iCurrentWidth'] - $aParams['iWMWidth'] - $aParams['iMargin'];
                    $iY = $aParams['iMargin'];

                    break;
                case Config::alignWatermarkBottomLeft:

                    $iX = $aParams['iMargin'];
                    $iY = $aParams['iCurrentHeight'] - $aParams['iWMHeight'] - $aParams['iMargin'];

                    break;

                default:
                case Config::alignWatermarkBottomRight:

                    $iX = $aParams['iCurrentWidth'] - $aParams['iWMWidth'] - $aParams['iMargin'];
                    $iY = $aParams['iCurrentHeight'] - $aParams['iWMHeight'] - $aParams['iMargin'];

                    break;
                case Config::alignWatermarkCenter:

                    $iX = ($aParams['iCurrentWidth'] - $aParams['iWMWidth']) / 2;
                    $iY = ($aParams['iCurrentHeight'] - $aParams['iWMHeight']) / 2;

                    break;
            }

            imagealphablending($oImage, true);
            imagecopy($oImage, $rWatermarkImage, (int) $iX, (int) $iY, 0, 0, $aParams['iWMWidth'], $aParams['iWMHeight']);
            imagealphablending($oImage, false);
        }

        return $oImage;
    }

    /**
     * Отдает изображение на коором в дальнейшем будет производиться "рисование".
     *
     * @param int $iWidth
     * @param int $iHeight
     * @param resource $oImage
     *
     * @return resource
     */
    public function getImage($iWidth, $iHeight, /* @noinspection PhpUnusedParameterInspection */$oImage)
    {
        return imagecreatetruecolor($iWidth, $iHeight);
    }
}
