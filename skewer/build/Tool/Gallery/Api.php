<?php

namespace skewer\build\Tool\Gallery;

use yii\base\Exception;

class Api
{
    public static function validateColor($sColor)
    {
        /*Попытаемся разобрать как формат #ffffff*/
        $matches = [];
        preg_match('/#[0-9,a,b,c,d,e,f]{6}/', $sColor, $matches, PREG_OFFSET_CAPTURE);

        if ((!empty($matches)) and (mb_strlen($sColor) == '7')) {
            return true;
        }

        /*Попытаемся разобрать как формат #fff*/
        $matches = [];
        preg_match('/#[0-9,a,b,c,d,e,f]{3}/', $sColor, $matches, PREG_OFFSET_CAPTURE);

        if ((!empty($matches)) and (mb_strlen($sColor) == '4')) {
            return true;
        }

        /*Попытаемся разобрать как формат rgb(255,255,255)*/
        $matches = [];
        preg_match('/rgb\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*\\)$/', $sColor, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches)) {
            for ($i = 1; $i <= 3; ++$i) {
                if ($matches[$i][0] > 255) {
                    throw new Exception(\Yii::t('gallery', 'Invalid_watermark_color'));
                }
            }

            return true;
        }

        preg_match('/rgba\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*\\,[0]\\.[0-9]{1}\\)$/', $sColor, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches)) {
            for ($i = 1; $i <= 3; ++$i) {
                if ($matches[$i][0] > 255) {
                    throw new Exception(\Yii::t('gallery', 'Invalid_watermark_color'));
                }
            }

            return true;
        }

        throw new Exception(\Yii::t('gallery', 'Invalid_watermark_color'));
    }
}
