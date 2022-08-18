<?php

namespace skewer\components\auth\firewall;

use skewer\base\orm\Query;

/**
 * Класс CRUD операций с моделями firewall_filters, firewall_actions, firewall_policy, firewall_reactions.
 *
 * @class skFirewallMapper
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 */
class FirewallMapper
{
    /**
     * Добавляет/обновляет фильтр
     *
     * @param array $aData массив данных согласно модели. Наличие ключа id говорит о том, что будет происходить обновление
     *
     * @return bool|mixed
     */
    public function saveFilter($aData)
    {
        if (!count($aData)) {
            return false;
        }

        $sQuery = sprintf(
            'INSERT INTO `firewall_filters` SET %s %s `active`=:active
            ON DUPLICATE KEY UPDATE %s `active`=:active;',
            (isset($aData['filter_id']) && $aData['filter_id'] ? '`filter_id`=:filter_id,' : ''),
            (isset($aData['filter']) ? '`filter`=:filter,' : ''),
            (isset($aData['filter']) ? '`filter`=:filter,' : '')
        );

        $oResult = Query::SQL($sQuery, $aData);

        return (isset($aData['filter_id']) && (int) $aData['filter_id']) ? $aData['filter_id'] : $oResult->lastId();
    }

    // func

    /**
     * Удаляет фильтр по ID.
     *
     * @param $iFilterId
     *
     * @return bool
     */
    public function removeFilter($iFilterId)
    {
        $sQuery = 'DELETE FROM `firewall_filters`  WHERE `filter_id`=?;';

        $oResult = Query::SQL($sQuery, $iFilterId);

        return (bool) $oResult;
    }

    // func

    /**
     * Добавляет/обновляет действие.
     *
     * @param array $aData массив данных согласно модели. Наличие ключа id говорит о том, что будет происходить обновление
     *
     * @return bool|mixed
     */
    public function saveAction($aData)
    {
        if (!count($aData)) {
            return false;
        }

        $sQuery = sprintf(
            'INSERT INTO `firewall_actions` SET %s `action`=:action, `title`=:title
            ON DUPLICATE KEY UPDATE `action`=:action, `title`=:title;',
            (isset($aData['action_id']) && $aData['action_id'] ? '`action_id`=:action_id,' : '')
        );

        $oResult = Query::SQL($sQuery, $aData);

        return (isset($aData['action_id']) && (int) $aData['action_id']) ? $aData['action_id'] : $oResult->lastId();
    }

    // func

    /**
     * Удаляет действие по Id.
     *
     * @param $iActionId
     *
     * @return bool
     */
    public function removeAction($iActionId)
    {
        $sQuery = 'DELETE FROM `firewall_actions`  WHERE `action_id`=?;';

        $oResult = Query::SQL($sQuery, $iActionId);

        return (bool) $oResult;
    }

    // func

    /**
     * Сохраняет запись реакции.
     *
     * @param array $aData набор данных согласно модели
     *
     * @return bool
     */
    public function saveReaction($aData)
    {
        if (!count($aData)) {
            return false;
        }
        if (!isset($aData['filter_id']) or !$iFilterId = $aData['filter_id']) {
            return false;
        }
        if (!isset($aData['action_id']) or !$aData['action_id']) {
            return false;
        }

        $sQuery = 'SELECT MAX(priority) AS min_priority FROM `firewall_reactions` WHERE `filter_id`=?;';

        $iMinPriority = Query::SQL($sQuery, $iFilterId)->getValue('min_priority');

        $aData['priority'] = ($iMinPriority) ? ++$iMinPriority : 1;

        $sQuery = '
            INSERT INTO `firewall_reactions`
            SET
              `filter_id`=:filter_id,
              `action_id`=:action_id,
              `active`=:active,
              `priority`=:priority
            ON DUPLICATE KEY UPDATE
              `active`=:active,
              `priority`=:priority;';

        $oResult = Query::SQL($sQuery, $aData);

        return (bool) $oResult;
    }

    // func

    /**
     * Удаляет реакцию для фильтра $iFilterId с действием $iActionId.
     *
     * @param $iFilterId
     * @param $iActionId
     *
     * @return bool
     */
    public function removeReaction($iFilterId, $iActionId)
    {
        $sQuery = 'DELETE FROM `firewall_reactions` WHERE `action_id`=? AND `filter_id`=?;';

        $oResult = Query::SQL($sQuery, (int) $iActionId, (int) $iFilterId);

        return (bool) $oResult;
    }

    // func

    /**
     * Добавляет правило.
     *
     * @param array $aData согласно модели
     *
     * @return bool
     */
    public function savePolicyCondition($aData)
    {
        if (!count($aData)) {
            return false;
        }
        if (!isset($aData['filter_id']) or !$aData['filter_id']) {
            return false;
        }
        if (!isset($aData['policy_id']) or !$iPolicyId = $aData['policy_id']) {
            return false;
        }

        $sExpire = (!isset($aData['expire']) or $aData['expire'] !== null) ? '' : ', `expire`=:expire';

        $sQuery = 'SELECT MAX(priority) AS min_priority FROM `firewall_policy` WHERE `policy_id`=?;';

        $iMinPriority = Query::SQL($sQuery, $iPolicyId)->getValue('min_priority');

        $aData['priority'] = ($iMinPriority) ? ++$iMinPriority : 1;

        $sQuery = "
            INSERT INTO `firewall_policy`
            SET
              `filter_id`=:filter_id,
              `policy_id`=:policy_id,
              `direction`=:direction,
              `priority`=:priority,
              `active`=:active
              {$sExpire}
            ON DUPLICATE KEY UPDATE
              `direction`=:direction,
              `priority`=:priority,
              `active`=:active
              {$sExpire};";

        $oResult = Query::SQL($sQuery, $aData);

        return (bool) $oResult;
    }

    // func

    /**
     * Удаляет правило. В случае если не указан фильтр, будут удалены все фильтры, применимые к данной политике.
     *
     * @param int $iPolicyId Id политики доступа
     * @param null|int $iFilterId Id фильтра
     *
     * @return bool Возвращает true, если удаление прошло успешно либо false в противном случае
     */
    public function removePolicyCondition($iPolicyId, $iFilterId = null)
    {
        $iPolicyId = (int) $iPolicyId;

        if (!$iPolicyId) {
            return false;
        }

        $sQuery = 'DELETE FROM `firewall_policy` WHERE `policy_id`=:policy_id ' .
            ($iFilterId !== null ? ' AND `filter_id`=:filter_id' : '') . ';';

        $oResult = Query::SQL($sQuery, ['policy_id' => $iPolicyId, 'filter_id' => $iFilterId]);

        return (bool) $oResult;
    }

    /**
     * Возвращает массив фильтров, привязанных к политике $iPolicyId.
     *
     * @param $iPolicyId
     *
     * @return array|bool
     */
    public function getPolicyFilters($iPolicyId)
    {
        $sQuery = '
            SELECT
                fp.*,
                ff.filter as `filter`
            FROM
                `firewall_policy` AS fp
            INNER JOIN `firewall_filters` AS ff ON ff.filter_id=fp.filter_id
            WHERE
                fp.`policy_id`=? AND fp.`active`=1
            ORDER BY
                fp.`priority`
            ASC;';

        $rRes = Query::SQL($sQuery, $iPolicyId);

        $aItems = [];
        while ($aItem = $rRes->fetchArray()) {
            $aItems[] = $aItem;
        }

        return count($aItems) ? $aItems : false;
    }

    // func

    /**
     * Возвращает массив действий по $iFilterId.
     *
     * @param $iFilterId
     *
     * @return array|bool
     */
    public function getActionsByFilter($iFilterId)
    {
        $sQuery = '
                SELECT
                    fr.*,
                    fa.`action` as `action`,
                    fa.`title` as `title`
                FROM
                    `firewall_reactions` as fr
                INNER JOIN `firewall_actions` as fa ON fa.action_id = fr.action_id
                WHERE
                    fr.`filter_id`=? AND
                    fr.`active`=1
                ORDER BY
                    fr.`priority`
                ASC;';

        $rRes = Query::SQL($sQuery, $iFilterId);

        $aItems = [];
        while ($aItem = $rRes->fetchArray()) {
            $aItems[] = $aItem;
        }

        return count($aItems) ? $aItems : false;
    }

    // func
}// class
