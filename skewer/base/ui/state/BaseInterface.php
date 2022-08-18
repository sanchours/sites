<?php

namespace skewer\base\ui\state;

use skewer\base\ui;
use skewer\build\Cms;

/**
 * Базовая часть интерфейсов для всех состояний.
 */
interface BaseInterface
{
    /**
     * Добавляет кнопку в интерфейс
     *
     * @param ui\element\Button $oButton
     */
    public function addButton(ui\element\Button $oButton);

    /**
     * Установить название компонента.
     *
     * @param string $sTitle
     */
    public function setTitle($sTitle);

    /**
     * Меняет заголовок основной панели.
     *
     * @param string $sNewTitle
     */
    public function setPanelTitle($sNewTitle);

    /**
     * Задает инициализационный  массив для атопостроителя интерфейсов.
     *
     * @param Cms\Frame\ModulePrototype $oModule - ссылка на вызвавший объект
     */
    public function setInterfaceData(Cms\Frame\ModulePrototype $oModule);

    /**
     * Задает массив со служебными данными для проброса
     * Этот массив вернется с посылкой.
     *
     * @param array $aData - массив данных
     */
    public function setServiceData($aData);

    /**
     * Возвращает массив служебных данных.
     *
     * @return array
     */
    public function getServiceData();
}
