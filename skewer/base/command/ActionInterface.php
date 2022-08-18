<?php
/**
 * Интерфейс работы подчиненного элемента прототипа "Команда" (Command).
 */

namespace skewer\base\command;

interface ActionInterface
{
    /**
     * Выполнение команды.
     *
     * @throws \Exception
     */
    public function execute();

    /**
     * Откат команды.
     */
    public function rollback();
}
