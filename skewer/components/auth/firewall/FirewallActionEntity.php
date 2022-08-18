<?php

namespace skewer\components\auth\firewall;

/**
 * Сущность Действие.
 *
 * @class FirewallActionEntity
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class FirewallActionEntity extends FirewallEntity
{
    /**
     * Id сущности.
     *
     * @var int
     */
    protected $action_id = 0;

    /**
     * Указатель на действие (метод обратного вызова).
     *
     * @var null
     */
    protected $action;

    /**
     * Название действия.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Конструктор может принимать два параметра - Id действия и его название.
     *
     * @param null|int $iActionId
     * @param string $sTitle
     */
    public function __construct($iActionId = 0, $sTitle = '')
    {
        $this->action_id = (int) $iActionId;
        $this->title = $sTitle;
    }

    // construct

    /**
     * Возвращает Id действия.
     *
     * @return int
     */
    public function getId()
    {
        return $this->action_id;
    }

    // func

    /**
     * Устанавливает Id действия.
     *
     * @param $iActionId
     */
    public function setId($iActionId)
    {
        $this->action_id = (int) $iActionId;
    }

    // func

    /**
     * Устанавливает описание для метода обратного вызова.
     *
     * @param $sClass
     * @param $sMethod
     * @param array $aParams
     */
    public function setCallback($sClass, $sMethod, $aParams = [])
    {
        $aCallback = [
            'class' => $sClass,
            'method' => $sMethod,
            'params' => $aParams,
        ];

        $this->action = $aCallback;
    }

    // func

    /**
     * Устанавливает упакованное в строку описание метода обратного вызова.
     *
     * @param $sCallback
     */
    public function setCallbackString($sCallback)
    {
        $this->action = $sCallback;
    }

    // func

    /**
     * Возвращает опсание метода обратного вызова.
     */
    public function getCallback()
    {
        return $this->action;
    }

    // func

    /**
     * Возвращает название действия.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    // func

    /**
     * Устанавилвает название действия.
     *
     * @param $sTitle
     */
    public function setTitle($sTitle)
    {
        $this->title = $sTitle;
    }

    // func
}// class
