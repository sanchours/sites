<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 14.08.13
 * Time: 14:56
 * To change this template use File | Settings | File Templates.
 */

namespace skewer\base\ui\element;

abstract class ButtonPrototype
{
    /** @var string название */
    protected $title = '';

    /** @var string иконка */
    protected $icon = '';

    /** @var string имя состояния в php */
    protected $phpAction = '';

    /** @var string имя состяния в js */
    protected $jsState = '';

    /** @var mixed[] дополнительные данные */
    protected $aAddParam = [];

    /**
     * Отдает название (всплывающая подпись).
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Задает название (всплывающая подпись).
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Отдает класс иконки.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Задает класс иконки.
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Отдает имя php состояния.
     *
     * @return string
     */
    public function getPhpAction()
    {
        return $this->phpAction;
    }

    /**
     * Задает имя php состояния.
     *
     * @param string $phpAction
     */
    public function setPhpAction($phpAction)
    {
        $this->phpAction = $phpAction;
    }

    /**
     * Отдает имя js состояни.
     *
     * @return string
     */
    public function getJsState()
    {
        return $this->jsState;
    }

    /**
     * Задает имя js состояни.
     *
     * @param string $jsState
     */
    public function setJsState($jsState)
    {
        $this->jsState = $jsState;
    }

    /**
     * Добавление дополнительных данных.
     *
     * @param string $sName
     * @param mixed $mVal
     */
    public function setAddParam($sName, $mVal)
    {
        $this->aAddParam[$sName] = $mVal;
    }

    /**
     * Отдает дополнительные данные по имени.
     *
     * @param string $sName
     *
     * @return null|mixed
     */
    public function getAddParam($sName)
    {
        return isset($this->aAddParam[$sName]) ? $this->aAddParam[$sName] : null;
    }

    /**
     * Отдает все дополнительные данные.
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
     */
    public function setAddParamList($aList)
    {
        $this->aAddParam = $aList;
    }
}
