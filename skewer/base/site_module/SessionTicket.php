<?php

namespace skewer\base\site_module;

use skewer\helpers\Ticket;

/**
 * Класс работы с тикетами с переопределенным хранилищем
 */
class SessionTicket extends Ticket
{
    /**
     * корневой ключ в сессии для хранения тикетов.
     *
     * @var string
     */
    private $sSessionKey = '_tickets';

    /**
     * Создает экземпляр тикета с хранилищем в сессии с возможностью указания ключа сессии для хранения данных.
     *
     * @param string $sKey Корневой ключ в сессии для хранения тикетов (Необяз.)
     *
     * @return SessionTicket
     */
    public function __construct($sKey = '')
    {
        $sSettingsKey = \Yii::$app->getParam(['session', 'tickets', 'key']);
        if (!empty($sSettingsKey)) {
            $this->sSessionKey = $sSettingsKey;
        }

        if (!empty($sKey)) {
            $this->sSessionKey = $sKey;
        }

        return true;
    }

    // constructor

    /**
     * Добавляет данные тикета в хранилище.
     *
     * @param array $aData Тело создаваемого тикета
     *
     * @return bool
     */
    protected function addTicketExecutor($aData)
    {
        $_SESSION[$this->sSessionKey][$aData['hash']] = $aData;

        return true;
    }

    // func

    /**
     * Очищает сессионные данные.
     */
    public function clearSession()
    {
        unset($_SESSION[$this->sSessionKey]);
    }

    /**
     * Удаляет тикет $sTicket из хранилища.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return bool
     */
    protected function delTicketExecutor($sTicket)
    {
        unset($_SESSION[$this->sSessionKey][$sTicket]);

        return true;
    }

    // func

    /**
     * Возвращает тело тикета $sTicket из хранилища.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return bool
     */
    protected function getTicketExecutor($sTicket)
    {
        return (isset($_SESSION[$this->sSessionKey][$sTicket])) ? $_SESSION[$this->sSessionKey][$sTicket] : false;
    }

    // func

    /**
     * Осуществляет проверку тикета $sTicket на валидность по expire.
     *
     * @param string $sTicket хеш-ключ тикета
     *
     * @return bool
     */
    protected function checkTicketExecutor($sTicket)
    {
        return (isset($_SESSION[$this->sSessionKey][$sTicket])) ? $_SESSION[$this->sSessionKey][$sTicket]['expire'] : false;
    }

    // func

    /**
     * Обновляет expire тикета.
     *
     * @param array $aData Данные тикета
     *
     * @return bool
     */
    protected function updExpireExecutor($aData)
    {
        if (!isset($_SESSION[$this->sSessionKey][$aData['hash']])) {
            return false;
        }

        $_SESSION[$this->sSessionKey][$aData['hash']] = $aData;

        return true;
    }

    // func
}// class
