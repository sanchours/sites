<?php

namespace skewer\components\auth\firewall;

use ReflectionClass;
use ReflectionMethod;

/**
 * @class FirewallAction
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class FirewallAction
{
    /**
     * Экземпляр маппера.
     *
     * @var null|FirewallMapper
     */
    protected $oMapper;

    /**
     * Базовый класс для потомков-владельцев методов действий.
     *
     * @var string
     */
    protected $sCallbackParent = 'FirewallCallbackPrototype';

    public function __construct()
    {
        $this->oMapper = new FirewallMapper();
    }

    // constr

    /**
     * Возвращает имя родителя для классов-владельцев методов действий.
     *
     * @return string
     */
    public function getParentClass()
    {
        return $this->sCallbackParent;
    }

    // func

    /**
     * Устанавливает имя класса-родителя.
     *
     * @param $sParentClass
     *
     * @throws FirewallException
     *
     * @return mixed
     */
    public function setParentClass($sParentClass)
    {
        if (!class_exists($sParentClass)) {
            throw new FirewallException('Firewall error: Callback prototype class not exists!');
        }

        return $this->sCallbackParent = $sParentClass;
    }

    // func

    /**
     * Сохраняет действие.
     *
     * @param FirewallActionEntity $oAction
     *
     * @return bool|mixed
     */
    public function saveAction(FirewallActionEntity $oAction)
    {
        $aData = $oAction->getDataArray();

        if (isset($aData['action']) && is_array($aData['action'])) {
            $aData['action'] = json_encode($aData['action']);
        }

        return $this->oMapper->saveAction($aData);
    }

    // func

    /**
     * Удаляет действие.
     *
     * @param $iActionId
     *
     * @return bool
     */
    public function removeAction($iActionId)
    {
        if (!(int) $iActionId) {
            return false;
        }

        return $this->oMapper->removeAction($iActionId);
    }

    // func

    /**
     * Возвращает все действия, привязанные к фильтру $iFilterId.
     *
     * @param $iFilterId
     *
     * @return array|bool
     */
    public function getActionsByFilter($iFilterId)
    {
        if (!(int) $iFilterId) {
            return false;
        }

        if (!$aItems = $this->oMapper->getActionsByFilter($iFilterId)) {
            return false;
        }

        $aActions = [];
        foreach ($aItems as $aItem) {
            $oAction = new FirewallActionEntity($aItem['action_id'], $aItem['title']);
            $oAction->setCallbackString($aItem['action']);
            $aActions[] = $oAction;
        }// each policy entry

        return $aActions;
    }

    // func

    /**
     * Выполняет действие. Т.е. запускает метод обратного вызова действия.
     *
     * @param FirewallActionEntity $oAction
     *
     * @throws FirewallException
     *
     * @return mixed
     */
    public function executeAction(FirewallActionEntity $oAction)
    {
        $cCallback = $oAction->getCallback();

        $cCallback = json_decode($cCallback);
        $sClass = $cCallback->class;
        $sMethod = $cCallback->method;
        $aParams = $cCallback->params;

        /* Пытаемся получить описание класса */
        $oCalledClass = new ReflectionClass($sClass);

        if (!($oCalledClass instanceof ReflectionClass)) {
            throw new FirewallException('Firewall exec action error: Class [' . $sClass . '] not found!');
        }
        if (!$oCalledClass->isSubclassOf($this->sCallbackParent)) {
            throw new FirewallException('Firewall exec action error: Class [' . $sClass . '] not accessible!');
        }
        $oCalledMethod = new ReflectionMethod($sClass, $sMethod);

        /* Метод найден */
        if (!($oCalledMethod instanceof ReflectionMethod)) {
            throw new FirewallException('Firewall exec action error: Method [' . $sMethod . '] in class [' . $sClass . '] not found!');
        }
        /* И он публичный */
        if (!$oCalledMethod->isPublic()) {
            throw new FirewallException('Firewall exec action error: Method [' . $sMethod . '] in class [' . $sClass . '] not accessible!');
        }
        /* Пытаемся выполнить */
        $mResponse = $oCalledMethod->invokeArgs(new $sClass(), $aParams);

        return $mResponse;
    }

    // func

    public function getAction($iActionId)
    {
    }

    // func
}// class
