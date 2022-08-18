<?php

namespace skewer\components\tipograf;

use yii\web\ServerErrorHttpException;

class Api
{
    const ADDRESS = 'https://www.typograf.ru/webservice/';

    /**
     * Преобразует текст
     *
     * @param string $text
     *
     * @throws ServerErrorHttpException
     *
     * @return string
     */
    public static function transform($text)
    {
        $data = [
            'text' => $text,
            'xml' => self::getPreferences(),
            'chr' => 'UTF-8',
        ];

        $ch = curl_init(self::ADDRESS);
        if (!$ch) {
            throw new ServerErrorHttpException('Не удалось открыть соединение с сервисом');
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            curl_close($ch);
            throw new ServerErrorHttpException('При обращении к сервису произошла ошибка: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Отдает настройки для запроса к сервиса Типографа
     * справка по настройкам http://www.typograf.ru/webservice/about/.
     *
     * @throws ServerErrorHttpException
     *
     * @return string
     */
    private static function getPreferences()
    {
        $config = file_get_contents(__DIR__ . '/config/config.xml');
        if (!$config) {
            throw new ServerErrorHttpException('Не удалось получить настройки Типографа');
        }

        return $config;
    }
}
