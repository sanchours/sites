<?php

namespace skewer\helpers;

use mysqli_result;
use skewer\base\orm\Query;

/**
 * Класс для работы с тикетами.
 */
class Ticket
{
    /**
     * Массив данных (cData) для создаваемого тикета.
     *
     * @var array
     */
    protected $aStorage = [];

    /**
     * Секретное слово, которое участвует в генерации хеша для тикета.
     *
     * @var string
     */
    protected $sSecretWord = 'cf7776510479c47d91ffd98414540c49';

    /**
     * Содержит время жизни тикета в днях.
     *
     * @var int
     */
    protected $iDays = 0;

    /**
     * Содержит время жизни тикета в часах.
     *
     * @var int
     */
    protected $iHours = 0;

    /**
     * Internal method for generate hash-words.
     *
     * @return string
     */
    private function generateTicket()
    {
        return md5($this->sSecretWord . md5(date('Y-m-d H:i:s')) . random_int(0, 10000)) . dechex(microtime(true));
    }

    // func

    /**
     * Добавляет данные в создаваемый тикет
     *
     * @param string $sKey Ключ данных
     * @param mixed $sVal Данные
     *
     * @return bool
     */
    public function addKey($sKey, $sVal = '')
    {
        if (!$sKey) {
            return false;
        }

        $this->aStorage[$sKey] = (is_array($sVal) || is_object($sVal)) ? 'crypted_' . base64_encode(serialize($sVal)) : $sVal;

        return true;
    }

    // func

    /**
     * Устанавливает время жизни тикета в днях.
     *
     * @param int $iDays Количество дней
     *
     * @return bool
     */
    public function perDays($iDays)
    {
        $this->iDays = (int) $iDays;

        return true;
    }

    // func

    /**
     * Устанавливает время жизни тикета в часах.
     *
     * @param int $iHours Количество часов
     *
     * @return bool
     */
    public function perHours($iHours)
    {
        $this->iHours = (int) $iHours;

        return true;
    }

    // func

    /**
     * Добавляет ранее наполненный тикет в хранилище.
     *
     * @param string $sTicket хеш-ключ тикета. Указывается, если требуется обновить тикет
     *
     * @return bool
     */
    public function addTicket($sTicket = '')
    {
        $aData['hash'] = (empty($sTicket)) ? $this->generateTicket() : $sTicket; // Если сохраняемый тикет не указан в качестве параметра - создаем новый.
        $this->iHours += $this->iDays * 24;
        $aData['expire'] = date('Y-m-d H:i:s', strtotime('+' . $this->iHours . '  hours', strtotime(date('Y-m-d H:i:s'))));
        $aData['cdata'] = json_encode($this->aStorage);

        $iRes = $this->addTicketExecutor($aData);

        $this->aStorage = [];
        $this->iDays = 0;
        $this->iHours = 0;

        return ($iRes) ? $aData['hash'] : false;
    }

    // func

    /**
     * Удаляет тикет хеш-ключу.
     *
     * @param string $sTicket Хеш-ключ тикета
     *
     * @return bool|mysqli_result
     */
    public function delTicket($sTicket)
    {
        if (!$sTicket) {
            return false;
        }

        return $this->delTicketExecutor($sTicket);
    }

    // func

    /**
     * Осуществляет проверку тикета на валидность по expire.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return bool
     */
    public function checkTicket($sTicket)
    {
        if (!$sTicket) {
            return false;
        }

        $aData = $this->checkTicketExecutor($sTicket);

        if (!$aData) {
            return false;
        }

        if (date('Y-m-d H:i:s') <= $aData['expire']) {
            return true;
        } // тикет свежий

        $this->delTicket($sTicket); // удаляем протухший тикет

        return false;
    }

    // func

    /**
     * Осуществляет удаление всех просроченных по exipre тикетов.
     *
     * @return bool|mysqli_result
     */
    public function delExpire()
    {
        return $this->delExpireExecutor();
    }

    // func

    /**
     * Обновляет времея жизни для тикета $sTicket.
     *
     * @param string $sTicket хеш-ключ тикета
     * @param int $iHours Количество дополнительных часов жизни тикета
     *
     * @return bool|mysqli_result
     */
    public function updExpire($sTicket, $iHours)
    {
        $iHours = (int) $iHours;

        if (!$sTicket) {
            return false;
        }
        if (!$iHours) {
            return false;
        }

        $aData['hash'] = $sTicket;
        $aData['expire'] = date('Y-m-d H:i:s', strtotime("+{$iHours}  hours", strtotime(date('Y-m-d H:i:s'))));

        return $this->updExpireExecutor($aData);
    }

    // func

    /**
     * Возвращает данные тикета $sTicket.
     *
     * @param  string $sTicket хеш-ключ тикета
     *
     * @return bool
     */
    public function getTicketData($sTicket)
    {
        $aData = $this->getTicketExecutor($sTicket);
        if (!$aData) {
            return false;
        }
        $this->aStorage = json_decode($aData['cdata'], true);

        return $aData['expire'];
    }

    // func

    /**
     * Возвращает значения ключа $sKey ранее полученного тикета.
     *
     * @param string $sKey Ключ данных
     *
     * @return bool|mixed
     */
    public function getKey($sKey)
    {
        if (!count($this->aStorage)) {
            return false;
        }
        if (!isset($this->aStorage[$sKey])) {
            return false;
        }
        if ((mb_stripos($this->aStorage[$sKey], 'crypted_')) === false) {
            return $this->aStorage[$sKey];
        }

        return unserialize(base64_decode(mb_substr($this->aStorage[$sKey], 8, mb_strlen($this->aStorage[$sKey]))));
    }

    // func

    /**
     * Возвращает массив ключей ранее полученного тикета.
     *
     * @return array|bool
     */
    public function getKeysArray()
    {
        if (!count($this->aStorage)) {
            return false;
        }

        $aOut = [];

        foreach ($this->aStorage as $sKey => $sVal) {
            if ((mb_stripos($this->aStorage[$sKey], 'crypted_')) === false) {
                $aOut[$sKey] = $this->aStorage[$sKey];
            } else {
                $aOut[$sKey] = unserialize(base64_decode(mb_substr($this->aStorage[$sKey], 8, mb_strlen($this->aStorage[$sKey]))));
            }
        }// each

        return $aOut;
    }

    // func

    /**
     * Очищает данные создаваемого тикета (без записи в базу).
     *
     * @return bool
     */
    public function clearKeys()
    {
        $this->aStorage = [];

        return true;
    }

    // func

    /**
     * Очуществляет проверку на существование тикета $sTicket.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return bool Возвращает true? если тикет существует в хранилище либо false в случае его отсутствия
     */
    public function ticketIsExists($sTicket)
    {
        $aData = $this->checkTicketExecutor($sTicket);

        return (!$aData) ? false : true;
    }

    // func

    /**
     * Добавляет данные тикета в хранилище.
     *
     * @usedby Переопределяется в потомках класса. Не используется напрямую.
     *
     * @param array $aData Тело создаваемого тикета
     *
     * @return bool
     */
    protected function addTicketExecutor($aData)
    {
        $sQuery = '
            INSERT INTO
                `tickets`
            SET
                hash=:hash,
                expire=:expire,
                cdata=:cdata
            ON DUPLICATE KEY UPDATE
                expire=:expire,
                cdata=:cdata;';

        $res = Query::SQL($sQuery, $aData);

        return (bool) $res;
    }

    // func

    /**
     * Удаляет тикет $sTicket из хранилища.
     *
     * @usedby Переопределяется в потомках класса. Не используется напрямую.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return int
     */
    protected function delTicketExecutor($sTicket)
    {
        $oResult = Query::SQL(
            'DELETE FROM `tickets` WHERE hash=:hash;',
            ['hash' => $sTicket]
        );

        return $oResult->affectedRows();
    }

    /**
     * Возвращает тело тикета $sTicket из хранилища.
     *
     * @usedby Переопределяется в потомках класса. Не используется напрямую.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return []
     */
    protected function getTicketExecutor($sTicket)
    {
        $oResult = Query::SQL(
            'SELECT expire, cdata FROM `tickets` WHERE hash=:hash LIMIT 0, 1;',
            ['hash' => $sTicket]
        );

        return $oResult->fetchArray();
    }

    /**
     * Осуществляет проверку тикета $sTicket на валидность по expire.
     *
     * @usedby Переопределяется в потомках класса. Не используется напрямую.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return []
     */
    protected function checkTicketExecutor($sTicket)
    {
        $oResult = Query::SQL(
            'SELECT expire FROM `tickets` WHERE hash=:hash LIMIT 0, 1;',
            ['hash' => $sTicket]
        );

        return $oResult->fetchArray();
    }

    // func

    /**
     * Обновляет expire тикета.
     *
     * @usedby Переопределяется в потомках класса. Не используется напрямую.
     *
     * @param array $aData Данные тикета
     *
     * @return int
     */
    protected function updExpireExecutor($aData)
    {
        $oResult = Query::SQL(
            'UPDATE `tickets` SET expire=:expire WHERE hash=:hash;',
            $aData
        );

        return $oResult->affectedRows();
    }

    // func

    /**
     * Удаляет устаревшие по expire тикеты.
     *
     * @usedby Переопределяется в потомках класса. Не используется напрямую.
     *
     * @return int
     */
    protected function delExpireExecutor()
    {
        $oResult = \skewer\base\orm\Query::SQL(
            'DELETE FROM `tickets` WHERE expire < NOW();'
        );

        return $oResult->affectedRows();
    }

    // func
}// class
