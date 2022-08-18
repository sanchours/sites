<?php

namespace skewer\controllers;

use skewer\components\tipograf;

class TipografController extends Prototype
{
    public function actionIndex()
    {
        $text = \Yii::$app->request->post('text', '');
        if (!$text) {
            return $text;
        }

        return tipograf\Api::transform($text);
        exit;
    }
}
