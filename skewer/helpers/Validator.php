<?php

namespace skewer\helpers;

use ErrorException;

/**
 * Класс с методами проверки основных типов данных.
 */
class Validator
{
    /** максимальная длина имени площадки */
    const maxHostNameLen = 64;

    /**
     * Паттерн, используемый для проверки URL на правильность.
     * Содержит placeholder {schemes}? который может быть заменен на конкретный признак протокола
     * перед обработкой регулярным выражением.
     *
     * @var string
     *
     * @see validSchemes
     */
    protected static $sURLPattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9-]*)(\.[A-Z0-9][A-Z0-9-]*)+)/i';

    /**
     * Паттерн, используемый для проверки логинов на правильность.
     * В текущем виде доступен ввод только латиницы, цыфр и знаков дифиса и подчеркивания.
     *
     * @var string
     */
    protected static $sLoginPattern = '/^[-_a-z0-9]+$/i';

    /**
     * Список разрешенных в URL схем.
     *
     * @var array
     **/
    protected static $aValidSchemes = ['http', 'https'];

    /**
     * Схема, применяемая по-умолчанию.
     *
     * @var string
     */
    protected static $sDefaultScheme = 'http';

    /**
     * Максимально допустимый размер логина в символах.
     *
     * @var int
     */
    protected static $iMaxLoginSize = 255;

    /**
     * Минимально допустимый размер логина в символах.
     *
     * @var int
     */
    protected static $iMinLoginSize = 3;

    /* Methods */

    /**
     * Проверяет URL $sUrl на соответствие стандарту.
     *
     * @static
     *
     * @param string $sUrl
     *
     * @return bool|string
     */
    public static function isUrl($sUrl)
    {
        if (!is_string($sUrl) or mb_strlen($sUrl) > 2000) {// защита от DOS атак
            return false;
        }

        if (static::$sDefaultScheme !== null and mb_strpos($sUrl, '://') === false) {
            $sUrl = static::$sDefaultScheme . '://' . $sUrl;
        }

        $sURLPattern = str_replace(
            '{schemes}',
            '(' . implode('|', static::$aValidSchemes) . ')',
            static::$sURLPattern
        );

        if (preg_match($sURLPattern, $sUrl)) {
            return $sUrl;
        }

        return false;
    }

    /**
     * Проверяет валидность email адреса.
     *
     * @param string $sEmail строка для проверки
     *
     * @return bool
     */
    public static function isEmail($sEmail)
    {
        $idn = new \idna_convert(['idn_version' => 2008]);

        return (bool) filter_var($idn->encode($sEmail), FILTER_VALIDATE_EMAIL);
    }

    /**
     * Проверяет логин $sLogin на недопустимые символы.
     * Логином также сожет являться email.
     *
     * @static
     *
     * @param string $sLogin Проверяемый логин
     *
     * @return bool|string Возвращает текст логина в случае его корректности либо false в случае ошибки
     */
    public static function isLogin($sLogin)
    {
        // ограничения по длине и типу
        $iStrLen = mb_strlen($sLogin);
        if (!is_string($sLogin) or $iStrLen > static::$iMaxLoginSize) {
            return false;
        }
        if ($iStrLen < static::$iMinLoginSize) {
            return false;
        }

        if (self::isEmail($sLogin)) {
            return $sLogin;
        }

        if (preg_match(static::$sLoginPattern, $sLogin)) {
            return $sLogin;
        }

        return false;
    }

    /**
     * Проверяет имя на корректность в качестве имени площадки.
     *
     * @param $sName
     *
     * @return bool
     */
    public static function isValidHostName($sName)
    {
        return (bool) preg_match('/^[a-z][-a-z0-9]{0,' . (self::maxHostNameLen - 1) . '}$/', $sName);
    }

    /**
     * Разбирает Строку IP фильтра на IP-адрес и маску
     * Принимает параметры следующего вида:
     * xxx.xxx.xxx.xxx[/x][;xxx.xxx.xxx.xxx[/x]].
     *
     * @example
     * '*' - разрешает все адреса
     * '192.168.1.1' - разрешает конкретный адрес
     * '192.168.0.0/16' - разрешает все IP адреса в 192.168 с маской 255.255.0.0
     * '192.168.0.0/16;10.0.0.0/24' - разрешает все IP адреса в 192.168 с маской 255.255.0.0 или 10 с маской 255.0.0.0
     *
     * @param string $sFilter Валидная строка фильтра
     *
     * @throws ErrorException
     *
     * @return array Возвращает массив групп из 2-х элементов. ip-адрес и mask-маска подсети
     * Если маска не указана, то в соответствующем ключе будет null
     */
    protected static function parseIPFilter($sFilter)
    {
        $aFilter = [];

        $aIp = explode(';', $sFilter);

        foreach ($aIp as $sIp) {
            if ($sIp == '*') {
                $aFilter[0]['ip'] = '*';
                $aFilter[0]['mask'] = null;

                return $aFilter;
            }

            preg_match_all('
    		/^
    			(?<ipaddr>
    				\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}  # сам IPv4 адрес
    			)
    			(?:[\\/] 				# Эта группа нам в результате не нужна
    				(?<mask>[0-9]{1,2}) # маска, если есть (возможные значения 1 - 32)
    			 )?
    		$/xUi', $sIp, $aEntry, PREG_SET_ORDER);

            if (!count($aEntry)) {
                throw new ErrorException('Error: Wrong filter format!');
            }
            $aEntry = $aEntry[0];

            $aItemFilter['ip'] = (isset($aEntry['ipaddr']) && !empty($aEntry['ipaddr'])) ? $aEntry['ipaddr'] : null;
            $aItemFilter['mask'] = (isset($aEntry['mask']) && !empty($aEntry['mask'])) ? (int) $aEntry['mask'] : null;

            $aFilter[] = $aItemFilter;
        } // foreach

        return $aFilter;
    }

    // func

    /**
     * Проверяет $IP  на вхождение в диапазон $sFilter. Возвращает true, если $IP является валидным в диапазоне,
     * указанном в $sFilter либо false в противном случае.
     *
     * @param string  $IP Валидная запись ip-адреса
     * @param string  $sFilter Валидная запись фильтра
     *
     * @throws ErrorException
     *
     * @return bool
     */
    public static function checkIP($IP, $sFilter)
    {
        $aFilter = self::parseIPFilter($sFilter);

        $IP = ip2long($IP);

        foreach ($aFilter as $aItem) {
            $Mask = (int) $aItem['mask'];
            $FilterIP = $aItem['ip'];

            /* Любой IP */
            if ($FilterIP == '*') {
                return true;
            }

            $FilterIP = ip2long($FilterIP);

            /* Конкретный IP */
            if ($FilterIP and !$Mask and $IP == $FilterIP) {
                return true;
            }

            /* Подсеть */
            if (0 > $Mask or $Mask > 32) {
                throw new ErrorException('Error: Wrong submask value!');
            }
            $subMask = (int) (2 ** 32 - 2 ** $Mask);

            if (($FilterIP & $subMask) == ($IP & $subMask)) {
                return true;
            }
        }

        return false;
    }
}
