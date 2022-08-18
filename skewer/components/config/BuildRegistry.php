<?php

namespace skewer\components\config;

use yii\base\Event;

/**
 * Конфигурация сборки.
 */
class BuildRegistry extends Prototype
{
    /** Вид отображения по умолчанию */
    const DEFAULT_TYPE = 'default';

    /**
     * Загружает набор данных.
     *
     * @throws Exception
     */
    protected function loadData()
    {
        $aData = Registry::getStorage();
        if (!$aData) {
            throw new Exception('Build storage is empty');
        }
        $this->setData($aData);
    }

    /**
     * Отдает путь до модуля.
     *
     * @param string $sModule имя модуля
     * @param string $sLayer имя слоя
     *
     * @return null|mixed
     */
    private function getModulePath($sModule, $sLayer)
    {
        return [
            Vars::LAYERS,
            $sLayer,
            Vars::MODULES,
            $sModule,
        ];
    }

    /**
     * Отдает конфиг модуля.
     *
     * @param string $sModule имя модуля
     * @param string $sLayer имя слоя
     *
     * @throws Exception
     *
     * @return ModuleConfig
     */
    public function getModuleConfig($sModule, $sLayer)
    {
        if (!$this->moduleExists($sModule, $sLayer)) {
            throw new Exception("Module [{$sModule}] not found in layer [{$sLayer}]");
        }

        return new ModuleConfig($this->get($this->getModulePath($sModule, $sLayer)));
    }

    /**
     * Отдает параметр конфигурации модуля.
     *
     * @param string $sParamPath путь до параметра в конфиге
     * @param string $sModule имя модуля
     * @param string $sLayer имя слоя
     *
     * @return null|mixed
     */
    public function getModuleConfigParam($sParamPath, $sModule, $sLayer)
    {
        $aPath = $this->getModulePath($sModule, $sLayer);
        $aPath[] = $sParamPath;

        return $this->get(implode($this->getDelimiter(), $aPath));
    }

    /**
     * Отдает флаг наличия модуля.
     *
     * @param string $sModule имя модуля
     * @param string $sLayer
     * @param string $sLayer имя слоя
     *
     * @return bool
     */
    public function moduleExists($sModule, $sLayer)
    {
        return $this->exists($this->getModulePath($sModule, $sLayer));
    }

    /**
     * Отдает набор событий.
     *
     * @param string $sEventName имя события
     *
     * @return array
     */
    public function getEvents($sEventName)
    {
        $aEvents = $this->get([
            Vars::EVENTS,
            $sEventName,
        ]);

        return $aEvents ?: [];
    }

    /**
     * Отдает dct зарегистрированные события.
     *
     * @return array
     */
    public function getAllEvents()
    {
        return $this->get([Vars::EVENTS]) ?: [];
    }

    /**
     * @throws Exception
     */
    public function getAllCleanup()
    {
        $aCleanup = [];

        $aLayerList = $this->getLayerList();
        foreach ($aLayerList as $layer) {
            $aModuleList = $this->getModuleList($layer);
            foreach ($aModuleList as $module) {
                $config = $this->getModuleConfig($module, $layer);
                $aData = $config->getData();
                if (isset($aData['cleanup'])) {
                    $aCleanup[$aData['cleanup']['type']][] = $aData['cleanup']['cleanupClass'];
                    if (isset($aData['cleanup']['specialDirectories'])) {
                        $aCleanup['specialDirectories'][$aData['cleanup']['cleanupClass']] = $aData['cleanup']['specialDirectories'];
                    }
                }
            }
        }

        return $aCleanup;
    }

    /**
     * Отдает набор функциональных политик модуля.
     *
     * @param string $sModule
     * @param string $sLayer
     *
     * @return array
     */
    public function getFuncPolicyItems($sModule, $sLayer)
    {
        $aList = $this->get([
            Vars::POLICY,
            $sLayer,
            $sModule,
            Vars::POLICY_ITEMS,
        ]);

        return is_array($aList) ? $aList : [];
    }

    /**
     * Отдает набор имен слоев.
     *
     * @return string[]
     */
    public function getLayerList()
    {
        $aLayers = $this->get(Vars::LAYERS);

        return is_array($aLayers) ? array_keys($aLayers) : [];
    }

    /**
     * Отдает имен модулей слоя.
     *
     * @param string $sLayer имя слоя
     *
     * @return string[]
     */
    public function getModuleList($sLayer)
    {
        $aModules = $this->get([
            Vars::LAYERS,
            $sLayer,
            Vars::MODULES,
        ]);

        return is_array($aModules) ? array_keys($aModules) : [];
    }

    /**
     * Обновляет данные конфигурации и словарей всех установленных в системе модулей.
     */
    public function actualizeAllModuleConfig()
    {
        $installer = new installer\Api();
        $installer->updateAllModulesConfig();
    }

    /**
     * Инициализаця событий из реестра.
     */
    public function initEvents()
    {
        foreach ($this->getAllEvents() as $eventName => $aEventList) {
            foreach ($aEventList as $aEvent) {
                // если есть указание прослушиваемого класса
                if (isset($aEvent[Vars::EVENT_TO_CLASS])) {
                    // вешаем обработчик на событие конкретного класса
                    Event::on(
                        $aEvent[Vars::EVENT_TO_CLASS],
                        $eventName,
                        [
                            $aEvent[Vars::EVENT_CLASS],
                            $aEvent[Vars::EVENT_METHOD],
                        ]
                    );
                }

                // иначе вешаем глобальное событие
                else {
                    \Yii::$app->on(
                        $eventName,
                        [
                            $aEvent[Vars::EVENT_CLASS],
                            $aEvent[Vars::EVENT_METHOD],
                        ]
                    );
                }
            }
        }
    }
}
