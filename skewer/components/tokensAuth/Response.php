<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:47.
 */

namespace skewer\components\tokensAuth;

class Response
{
    /**
     * Мод вывода.
     *
     * @var string
     */
    public static $sMode = 'show';

    /**
     * Выходные данные.
     *
     * @var null
     */
    public static $aData = null;

    /**
     * Отдача данных.
     */
    public static function execute()
    {
        if (self::$aData !== null) {
            if (isset(self::$aData['mode'])) {
                self::$sMode = self::$aData['mode'];
            }

            if (self::$sMode == 'show') {
                echo self::$aData['content'];
            } elseif (self::$sMode == 'redirect') {
                $sParams = '';
                $aParams = [];
                if (isset(self::$aData['params'])) {
                    foreach (self::$aData['params'] as $name => $param) {
                        $aParams[] = $name . '=' . $param;
                    }
                    $sParams = implode('&', $aParams);
                    if ($sParams !== '') {
                        $sParams = '?' . $sParams;
                    }
                }

                header('Location: ' . self::$aData['redirect_link'] . $sParams);
            } elseif (self::$sMode == 'none') {
            }
        }
        exit;
    }
}
