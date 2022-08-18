<?php

namespace skewer\components\auth\firewall;

/**
 * реализует методы для работы с фильтрами.
 *
 * @class FirewallFilter
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class FirewallFilter
{
    /**
     * Экземпляр маппера.
     *
     * @var null|FirewallMapper
     */
    protected $oMapper;

    public function __construct()
    {
        $this->oMapper = new FirewallMapper();
    }

    // constr

    /**
     * Сохраняет фильтр (добавляет либо обновляет).
     *
     * @param FirewallFilterEntity $oFilter
     *
     * @return bool|mixed
     */
    public function saveFilter(FirewallFilterEntity $oFilter)
    {
        $aData = $oFilter->getDataArray();

        return $this->oMapper->saveFilter($aData);
    }

    // func

    /**
     * Удаляет фильтр по $iFilterId.
     *
     * @param $iFilterId
     *
     * @return bool
     */
    public function removeFilter($iFilterId)
    {
        if (!(int) $iFilterId) {
            return false;
        }

        return $this->oMapper->removeFilter($iFilterId);
    }

    // func

    /**
     * Возвращает список фильтров, привязанных к политике безопасности $iPolicyId.
     *
     * @param $iPolicyId
     *
     * @return array|bool
     */
    public function getFiltersByPolicy($iPolicyId)
    {
        if (!(int) $iPolicyId) {
            return false;
        }

        if (!$aItems = $this->oMapper->getPolicyFilters($iPolicyId)) {
            return false;
        }

        $oActions = new FirewallAction();

        $aFilters = [];
        foreach ($aItems as $aItem) {
            $oFilter = new FirewallFilterEntity($aItem['filter_id'], $aItem['filter'], 1);

            $oFilter->setActions($oActions->getActionsByFilter($oFilter->getId()));

            $aFilters[] = [
                'expire' => ($aItem['expire'] === null) ? false : $aItem['expire'],
                'filter' => $oFilter,
                'direction' => $aItem['direction'],
            ];
        }// each policy entry

        return $aFilters;
    }

    // func

    public function getFilter($iFilterId)
    {
    }

    // func

    public function setActive($iFilterId, $bActive = true)
    {
    }

    // func

    public function getActions($iFilterId)
    {
    }

    // func
}// class
