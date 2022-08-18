<?php

namespace skewer\components\auth\firewall;

use skewer\helpers\Validator;

/**
 * Класс для тонкой настройки политик доступа по ip.
 */
class Firewall
{
    /**
     *  Определяет направление фильтра в контексте привязки к политике.
     *
     *  @const dtAllow int Разрешить в случае совпадения
     */
    const dtAllow = 1;

    /**
     *  Определяет направление фильтра в контексте привязки к политике.
     *
     *  @const dtDeny int Запретить в случае совпадения
     */
    const dtDeny = 0;

    /**
     * Объект работы с сущностями действий.
     *
     * @var null|FirewallAction
     */
    protected $oActions;

    /**
     * Объект работы с сущностями фильтров.
     *
     * @var null|FirewallFilter
     */
    protected $oFilters;

    /**
     * Экземпляр маппера.
     *
     * @var null|FirewallMapper
     */
    protected $oMapper;

    /**
     * Флаг статуса, возвращаемого файерволом для всех проверок в случае его диактивации.
     *
     * @var bool
     */
    protected $bLock = false;

    /**
     * Флаг активности. Если установлен в false, то все проверки файервола будут проходить успешно либо неуспешно
     * в зависимости от флага  bLock.
     *
     * @var bool
     *
     * @example
     * ---------------------------------
     * | bLock | bEnable | check_result|
     * |   1   |    1    |      X      |
     * |   1   |    0    |      1      |
     * |   0   |    0    |      0      |
     * |   0   |    1    |      X      |
     * ---------------------------------
     */
    protected $bEnable = true;

    public function __construct()
    {
        $this->oActions = new FirewallAction();
        $this->oFilters = new FirewallFilter();
        $this->oMapper = new FirewallMapper();
    }

    // constr

    /**
     * Добавляет в файервол новое действие.
     *
     * @param $sTitle string Название действия
     * @param string $sClass Класс-владелец метода обратного вызова
     * @param string $sMethod Метод обратного вызова
     * @param array $aParams Параметры запуска метода
     *
     * @return bool|mixed Возвращает Id нового действия либо false
     */
    public function addAction($sTitle, $sClass, $sMethod, $aParams = [])
    {
        $oAction = new FirewallActionEntity(null, $sTitle);
        $oAction->setCallback($sClass, $sMethod, $aParams);

        return $this->saveAction($oAction);
    }

    /**
     * Добавляет объект действия.
     *
     * @param FirewallActionEntity $oAction
     *
     * @return bool|mixed
     */
    public function saveAction(FirewallActionEntity $oAction)
    {
        return $this->oActions->saveAction($oAction);
    }

    /**
     * Возвращает Класс-прототип для потомков-владельцев методов обратного вызова.
     *
     * @return string
     */
    protected function getParentClass()
    {
        return $this->oActions->getParentClass();
    }

    // func

    /**
     * Устанавлвает Класс-прототип для потомков-владельцев методов обратного вызова.
     *
     * @param $sParentClass
     *
     * @return mixed
     */
    protected function setParentClass($sParentClass)
    {
        return $this->oActions->setParentClass($sParentClass);
    }

    // func

    /**
     * Добавляет фильтр в настройки файервола.
     *
     * @param $sFilter string Строка фильтра в допустимом формате
     * @param bool $bActive
     *
     * @return bool|mixed
     */
    public function addFilter($sFilter, $bActive = true)
    {
        $oFilter = new FirewallFilterEntity(0, $sFilter, $bActive);

        return $this->saveFilter($oFilter);
    }

    /**
     * Сохраняет фильтр
     *
     * @param FirewallFilterEntity $oFilter
     *
     * @return bool|mixed
     */
    public function saveFilter(FirewallFilterEntity $oFilter)
    {
        return $this->oFilters->saveFilter($oFilter);
    }

    /**
     * Добавляет реакцию к фильтру.
     *
     * @param $iFilterId int Id фильтра
     * @param $iActionId int Id действия
     * @param bool $bActive
     */
    public function addReaction($iFilterId, $iActionId, $bActive = true)
    {
        $aData['filter_id'] = $iFilterId;
        $aData['action_id'] = $iActionId;
        $aData['active'] = $bActive;
        $this->oMapper->saveReaction($aData);
    }

    // func

    /**
     * Добавляет правило $iFilterId для политики безопасности $iPolicyId.
     *
     * @param $iFilterId int Id фильтра
     * @param $iPolicyId int Id политики безопасности
     * @param $iDirection int Направление результата проверки фильтра (позитивный либо негативный)
     * @param bool $bActive bool Активность правила
     * @param null $sExpire str Дата и время конца действия фильтра
     *
     * @return bool Возвращет true? если правило добавлено либо false в случае ошибки
     */
    public function addRule($iFilterId, $iPolicyId, $iDirection, $bActive = true, $sExpire = null)
    {
        $aData['expire'] = $sExpire;
        $aData['active'] = (int) $bActive;
        $aData['filter_id'] = $iFilterId;
        $aData['policy_id'] = $iPolicyId;
        $aData['direction'] = (int) $iDirection;

        return $this->oMapper->savePolicyCondition($aData);
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
    public function removeRule($iPolicyId, $iFilterId = null)
    {
        return $this->oMapper->removePolicyCondition($iPolicyId, $iFilterId);
    }

    /**
     * Проверяет доступ по IP $sClientIP к политике $iPolicyId.
     *
     * @param $sClientIP - строка IP-адреса пользователя
     * @param $iPolicyId - Id существующей политики безопасности
     *
     * @return bool Возвращает true? если доступ разрешен либо false в противном случае
     */
    public function checkAccess($sClientIP, $iPolicyId)
    {
        if (!$this->bEnable) {
            return $this->bLock;
        }

        $aFilters = $this->oFilters->getFiltersByPolicy($iPolicyId);

        if (!$aFilters) {
            return true;
        }

        /* @var $oFilter FirewallFilterEntity */
        foreach ($aFilters as $aItem) {
            $oFilter = $aItem['filter'];
            if (!($oFilter instanceof FirewallFilterEntity)) {
                return false;
            }

            if (Validator::checkIP($sClientIP, $oFilter->getFilter()) xor $aItem['direction']) {
                return false;
            }

            if ($oFilter->getActions()) {
                foreach ($oFilter->getActions() as $oAction) {
                    $this->oActions->executeAction($oAction);
                }
            }
        }// each filter for policy

        return true;
    }

    // func

    public function getUserId($sClientIP, $iPolicyId, $defaultUserId)
    {
        if (!$this->bEnable) {
            return $this->bLock;
        }

        $aFilters = $this->oFilters->getFiltersByPolicy($iPolicyId);

        /* Нет фильтров - грузим default */

        if (!$aFilters) {
            return $defaultUserId;
        }

        $resultUser = $defaultUserId;

        foreach ($aFilters as $aItem) {
            /** @var $oFilter FirewallFilterEntity */
            $oFilter = $aItem['filter'];

            if (!($oFilter instanceof FirewallFilterEntity)) {
                return $defaultUserId;
            }

            $checkRes = Validator::checkIP($sClientIP, $oFilter->getFilter());

            /* Сработал лии фильтр - не сработал - все, возвращаем дефолта */
            if ($checkRes) {
                if ($aItem['direction']) {
                    if ($oFilter->getActions()) {
                        $aActions = $oFilter->getActions();
                        $resultUser = $this->oActions->executeAction($aActions[0]);
                    }
                } else {
                    $resultUser = $defaultUserId;
                }
            }
        }

        return $resultUser;
    }

    /**
     * Запускает либо останавливает fireweall. Если установлен в false, то все проверки файервола будут проходить успешно либо неуспешно
     * в зависимости от флага  $bReturnStatus.
     *
     * @param $bEnable
     * @param bool $bReturnStatus
     *
     * @example
     * ---------------------------------
     * | bLock | bEnable | check_result|
     * |   1   |    1    |      X      |
     * |   1   |    0    |      1      |
     * |   0   |    0    |      0      |
     * |   0   |    1    |      X      |
     * ---------------------------------
     */
    public function enable($bEnable, $bReturnStatus = true)
    {
        $this->bEnable = (bool) $bEnable;
        $this->bLock = (bool) $bReturnStatus;
    }
}// class
