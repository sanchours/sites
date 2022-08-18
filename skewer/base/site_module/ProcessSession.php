<?php

namespace skewer\base\site_module;

/**
 * Хранилище дерева процессов.
 */
class ProcessSession
{
    /**
     * Экземпляр класса SessionTicket.
     *
     * @var null|SessionTicket
     */
    private $oTicket;

    /**
     * Создает экземпляр провайдера к хранилищу дерева процессов.
     *
     * @param string $sKeyName - имя ключа
     */
    public function __construct($sKeyName = 'key')
    {
        $this->oTicket = new SessionTicket(\Yii::$app->getParam(['session', 'process', $sKeyName]));
    }

    // constructor

    /**
     * Загружает дерево процессов по идентификатору сессии.
     *
     * @param string $sTicket Хеш-ключ идентификатора сессии
     *
     * @return bool|mixed
     */
    public function load($sTicket)
    {
        $sExpire = $this->oTicket->getTicketData($sTicket);
        if (!$sExpire) {
            return false;
        }
        $oProcess = $this->oTicket->getKey('process');

        return (($oProcess instanceof Process)) ? $oProcess : false;
    }

    // func

    /**
     * Сохраняет процесс / дерево процессов в сессионное хранилище.
     *
     * @param Process $oProcess Корневой сохраняемый процесс
     * @param bool $sSessionId Указатель на ключ хранилища сессии. Используется если требуется обновить текущий тикет.
     *
     * @return bool
     */
    public function save(Process $oProcess, $sSessionId = false)
    {
        $this->oTicket->clearKeys();
        $this->oTicket->addKey('process', $oProcess);

        return  $this->oTicket->addTicket($sSessionId);
    }

    // func

    /**
     * Осуществляет проверку наличия дерева процессов по тикету $sTicket.
     *
     * @param string $sTicket Хеш-ключ идентификатора сессии
     *
     * @return bool
     */
    public function isExists($sTicket)
    {
        return ($this->oTicket->ticketIsExists($sTicket)) ? true : false;
    }

    // func

    /**
     * Создает пустую сессию.
     *
     * @return bool В случае успеха возвращает хеш-ключ созданной сессии
     */
    public function createSession()
    {
        $this->oTicket->clearKeys();

        return  $this->oTicket->addTicket();
    }

    // func

    /**
     * Очищает текущее хранилище.
     *
     * @static
     */
    public static function flushStorage()
    {
        $oProcess = new self();
        $oProcess->oTicket->clearSession();
    }
}// class
