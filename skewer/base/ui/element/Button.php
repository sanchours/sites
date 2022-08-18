<?php

namespace skewer\base\ui\element;

use skewer\components\ext;

/**
 * Класс для описания кнопок интерфейса.
 */
class Button extends ButtonPrototype
{
    /** @var string подтверждение */
    protected $sConfirm = '';

    /** @var bool флаг проверки наличия изменений в форме */
    protected $bUseDirtyChecker = true;

    /**
     * Запрос подтверждения.
     *
     * @return string
     */
    public function getConfirm()
    {
        return $this->sConfirm;
    }

    /**
     * Установка подтверждения.
     *
     * @param string $sConfirm
     *
     * @return ext\docked\Prototype
     */
    public function setConfirm($sConfirm)
    {
        $this->sConfirm = $sConfirm;

        return $this;
    }

    /*
     * Проверка наличия изменений в форме
     */

    /**
     * Отдает статус наличия проверки изменений в форме.
     *
     * @return bool
     */
    public function getDirtyChecker()
    {
        return $this->bUseDirtyChecker;
    }

    /**
     * Устанавливает флаг проверки изменений в форме.
     *
     * @param bool $bVal значение
     *
     * @return ext\docked\Prototype
     */
    public function setDirtyChecker($bVal = true)
    {
        $this->bUseDirtyChecker = (bool) $bVal;

        return $this;
    }

    /**
     * Снимает флаг проверки изменений в форме.
     *
     * @return ext\docked\Prototype
     */
    public function unsetDirtyChecker()
    {
        $this->bUseDirtyChecker = false;

        return $this;
    }
}
