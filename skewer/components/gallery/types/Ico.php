<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 12.05.2017
 * Time: 14:37.
 */

namespace skewer\components\gallery\types;

use Elphin\IcoFileLoader\IcoFileService;
use skewer\libs\lordelph\GdRenderer;
use yii\base\UserException;

class Ico extends Png
{
    public function __construct()
    {
    }

    public function createGD($sFileName)
    {
        $sNewFileName = FILEPATH . 'temp/tmp' . random_int(0, 99999) . '.png';

        try {
            $oRenderer = new GdRenderer();
            $loader = new IcoFileService($oRenderer);
            $im = $loader->extractIcon($sFileName, 0, 0);
        } catch (\Exception $e) {
            throw new UserException(\Yii::t('gallery', 'ico_error'));
        }

        if (!file_exists(FILEPATH . 'temp/')) {
            mkdir(FILEPATH . 'temp/');
        }

        imagepng($im, $sNewFileName);

        $oImg = imagecreatefrompng($sNewFileName);

        unlink($sNewFileName);

        return $oImg;
    }

    public function createImg($oImg, $sFileName)
    {
        $sFileName = str_replace('.ico', '.png', $sFileName);

        imagepng($oImg, $sFileName ? $sFileName : null);

        return $sFileName;
    }

    public static function getNumType()
    {
        return '17';
    }
}
