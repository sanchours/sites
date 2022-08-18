<?php

namespace skewer\components\config;

/**
 * Прототип патча.
 */
abstract class PatchPrototype extends UpdateHelper
{
    /**
     * Описание патча.
     *
     * @var string
     */
    public $sDescription = '';

    /**
     * Набор сообщений ои патча после установки.
     *
     * @var string[]
     */
    protected $aMessages = [];

    /**
     * Флаг необходимости перестроения кэша
     * Пересобираются:
     *  * конфиги модулей
     *  * языковые метки
     *  * css настройки.
     *
     * @var bool
     */
    public $bUpdateCache = false;

    /**
     * Базовый метод запуска обновления.
     *
     * @throws \Exception
     * @throws UpdateException
     *
     * @return bool
     */
    abstract public function execute();

    /**
     * Добавляет сообщение, которое будет выведено после установки одиночного патча.
     *
     * @param $sMessage
     */
    protected function addMessage($sMessage)
    {
        $this->aMessages[] = $sMessage;
    }

    /**
     * Отдает набор сообщений ои патча после установки.
     *
     * @return string[]
     */
    public function getMessages()
    {
        return $this->aMessages;
    }
}// class
