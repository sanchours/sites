<?php

namespace skewer\components\ext\docked;

/**
 * Класс прототип кнопок интерфейса
 * deprecated может быть замещено skewer\base\ui\element\Button.
 */
abstract class Prototype
{
    /** @var string подпись кнопки */
    protected $sTitle = '';

    /** @var string состояние в php, вызываемое по нажатию */
    protected $sAction = '';

    /** @var string состояние в js, вызываемое по нажатию */
    protected $sState = '';

    /** @var string иконка */
    protected $sIconCls = '';

    /** @var string подтверждение */
    protected $sConfirm = '';

    /** @var array доп параметры */
    protected $aAddParam = [];

    /** @var bool флаг проверки наличия изменений в форме */
    protected $bUseDirtyChecker = true;

    /**
     * Закрытый конструктор
     */
    protected function __construct()
    {
    }

    /**
     * Отдает инициализационный массив.
     *
     * @return array
     */
    public function getInitArray()
    {
        return [
            'text' => $this->getTitle(),
            'iconCls' => $this->getIconCls(),
            'state' => $this->getState(),
            'action' => $this->getAction(),
            'addParams' => $this->getAddParamList(),
            'confirmText' => $this->getConfirm(),
            'unsetFormDirtyBlocker' => !$this->getDirtyChecker(),
        ];
    }

    /**
     * Возвращает подпись кнопки.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->sTitle;
    }

    /**
     * Задает подпись кнопки.
     *
     * @param string $sTitle
     *
     * @return Prototype
     */
    public function setTitle($sTitle)
    {
        $this->sTitle = $sTitle;

        return $this;
    }

    /**
     * Отдает php состояние.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->sAction;
    }

    /**
     * Задает php состояние.
     *
     * @param string $sAction
     *
     * @return Prototype
     */
    public function setAction($sAction)
    {
        $this->sAction = $sAction;

        return $this;
    }

    /**
     * Отдает js состояние.
     *
     * @return string
     */
    public function getState()
    {
        return $this->sState;
    }

    /**
     * Задает js состояние.
     *
     * @param string $sState
     *
     * @return Prototype
     */
    public function setState($sState)
    {
        $this->sState = $sState;

        return $this;
    }

    /**
     * Отдает иконку.
     *
     * @return string
     */
    public function getIconCls()
    {
        return $this->sIconCls;
    }

    /**
     * Задает иконку.
     *
     * @param string $sIconCls
     *
     * @return Prototype
     */
    public function setIconCls($sIconCls)
    {
        $this->sIconCls = $sIconCls;

        return $this;
    }

    /**
     * Возвращает доп параметры.
     *
     * @return array
     */
    public function getAddParamList()
    {
        return $this->aAddParam;
    }

    /**
     * Задает набор доп параметры.
     *
     * @param array $aList
     *
     * @return Prototype
     */
    public function setAddParamList($aList)
    {
        $this->aAddParam = $aList;

        return $this;
    }

    /**
     * Возвращает доп параметры.
     *
     * @param $sParamName
     *
     * @return array
     */
    public function getAddParam($sParamName)
    {
        return isset($this->aAddParam[$sParamName]) ? $this->aAddParam[$sParamName] : false;
    }

    /**
     * Задает подпись кнопки.
     *
     * @param $sParamName
     * @param $sValue
     *
     * @return Prototype
     */
    public function setAddParam($sParamName, $sValue)
    {
        $this->aAddParam[$sParamName] = $sValue;

        return $this;
    }

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
     * @return Prototype
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
     * @return Prototype
     */
    public function setDirtyChecker($bVal = true)
    {
        $this->bUseDirtyChecker = (bool) $bVal;

        return $this;
    }

    /**
     * Снимает флаг проверки изменений в форме.
     *
     * @return Prototype
     */
    public function unsetDirtyChecker()
    {
        $this->bUseDirtyChecker = false;

        return $this;
    }
}
