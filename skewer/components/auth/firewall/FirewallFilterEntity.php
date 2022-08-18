<?php

namespace skewer\components\auth\firewall;

/**
 * Сущность Фильтр
 *
 * @class FirewallFilterEntity
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class FirewallFilterEntity extends FirewallEntity
{
    /**
     * Идентификатор фильтра.
     *
     * @var int
     */
    protected $filter_id = 0;

    /**
     * Текст фильтра.
     *
     * @var string
     */
    protected $filter = '';

    /**
     * Флаг активности.
     *
     * @var bool
     */
    protected $active = true;

    /**
     * Конструктор может принимать в качестве входных параметров для создания фильтра id, текст фильтра, флаг активности
     * и массив объектов действий, привязанных к фильтру.
     *
     * @param int $iFilterId
     * @param string $sFilter
     * @param bool $bActive
     * @param array $aActions
     */
    public function __construct($iFilterId = 0, $sFilter = '', $bActive = true, $aActions = [])
    {
        $this->filter_id = (int) $iFilterId;
        $this->filter = $sFilter;
        $this->active = $bActive;
        $this->aActions = $aActions;
    }

    // func

    /**
     * Возвращет ID фильтра.
     *
     * @return int
     */
    public function getId()
    {
        return $this->filter_id;
    }

    // func

    /**
     * Устанавливает ID фильтра.
     *
     * @param $iFilterId
     */
    public function setId($iFilterId)
    {
        $this->filter_id = (int) $iFilterId;
    }

    // func

    /**
     * Возвращает текст фильтра
     * Возвращает текст фильтра.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    // func

    /**
     * Устанавливает текст фильтра.
     *
     * @param $sFilter
     */
    public function setFilter($sFilter)
    {
        $this->filter = $sFilter;
    }

    // func

    /**
     * Возвращвет true, если текущий фильтр активен.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    // func

    /**
     * Устанавливает активность фильтру.
     *
     * @param $bActive
     */
    public function setActive($bActive)
    {
        $this->active = $bActive;
    }

    // func

    /**
     * Возвращает массив действий, привязанных к фильтру.
     *
     * @return array
     */
    public function getActions()
    {
        return (isset($this->aActions)) ? $this->aActions : [];
        //return static::$aActions;
    }

    // func

    /**
     * Устанавливает массив действий для фильтра.
     *
     * @param $aActions
     *
     * @return mixed
     */
    public function setActions($aActions)
    {
        return $this->aActions = $aActions;
    }

    // func

    /**
     * Добавляет объект действия к фильтру.
     *
     * @param firewallActionEntity $oAction
     */
    public function appendAction(firewallActionEntity $oAction)
    {
        if (!isset($this->aActions)) {
            $this->aActions = [];
        }
        $this->aActions[] = $oAction;
    }

    // func
}// class
