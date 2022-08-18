<?php

namespace skewer\helpers;

use yii\base\UserException;

/**
 * Класс для автоматического машинного текста
 * Используется сервис Яндекс.Переводчик.
 */
class Translate
{
    /**
     * Перевести строку.
     *
     * @param $string
     * @param string $from_lng
     * @param string $to_lang
     *
     * @throws UserException
     *
     * @return mixed
     */
    public static function translate($string, $from_lng = 'ru', $to_lang = 'en')
    {
        $string = urlencode($string);

        $yandex_translate_key = \Yii::$app->getParam('yandex_translate_key', '');
        if (!$yandex_translate_key) {
            throw new UserException('Config param "yandex_translate_key" not found');
        }
        $request = @file_get_contents('https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $yandex_translate_key . '&text=' . $string . '&lang=' . $from_lng . '-' . $to_lang);

        if (!$request) {
            throw new UserException('Fail to translate. check param "yandex_translate_key"');
        }
        $array = json_decode($request, true);
        $text = $array['text'][0];

        return $text;
    }
}
