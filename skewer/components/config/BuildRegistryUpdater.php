<?php

namespace skewer\components\config;

/**
 * Конфигурация сборки c возможностью обновления
 * Схему можно посмотреть в планшете MindJet::BuildConfig.
 */
class BuildRegistryUpdater extends UpdatePrototype
{
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
     * Сохраняет данные.
     *
     * @return bool
     */
    protected function saveData()
    {
        Registry::saveStorage($this->getData());
    }

    /**
     * Устанавливает набор данных модуля в реестр сборки.
     *
     * @param ModuleConfig $oModuleConfig
     *
     * @throws Exception
     */
    public function registerModule(ModuleConfig $oModuleConfig)
    {
        try {
            $aModulePath = $this->getModulePath(
                $oModuleConfig->getName(),
                $oModuleConfig->getLayer()
            );

            if ($this->exists($aModulePath)) {
                throw new Exception('Module already exists');
            }
            $oModuleConfig->clearUnwantedData();

            $this->set($aModulePath, $oModuleConfig->getData());

            $this->addEventList($oModuleConfig);

            $this->addPolicyList($oModuleConfig);
        } catch (Exception $e) {
            throw new Exception(
                sprintf(
                    '"%s" for module [%s] in layer [%s]',
                    $e->getMessage(),
                    $oModuleConfig->getName(),
                    $oModuleConfig->getLayer()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Добавляет набор событий заданного модуля в реестр
     *
     * @param ModuleConfig $oModuleConfig
     *
     * @throws Exception
     *
     * @return int число добавленных событий
     */
    private function addEventList(ModuleConfig $oModuleConfig)
    {
        $iCnt = 0;

        // список событий в конфиге модуля
        $aEvents = $oModuleConfig->getVal(Vars::EVENTS);
        if (!is_array($aEvents)) {
            return 0;
        }

        foreach ($aEvents as $sEventName => $aHandler) {
            // проверка ключа. числовой - значит имя внетири массива
            if (is_numeric($sEventName)) {
                // нет имени - ошибка
                if (!isset($aHandler[Vars::EVENT_NAME])) {
                    throw new Exception('no `event` (event name) val in handler');
                }
                // есть - подменяем для дольнеёшего использования
                $sEventName = $aHandler[Vars::EVENT_NAME];
            }

            // проверяем наличие класса с обработчиком события
            if (!isset($aHandler[Vars::EVENT_CLASS])) {
                throw new Exception('no `class` val in handler for event `' . $sEventName . '`');
            }
            // проверка наличия имени метода для вызова
            if (!isset($aHandler[Vars::EVENT_METHOD])) {
                throw new Exception('no `method` val in handler event `' . $sEventName . '`');
            }
            // проверка доступности вызова указанного метода из класса
            if (!is_callable([$aHandler[Vars::EVENT_CLASS], $aHandler[Vars::EVENT_METHOD]])) {
                throw new Exception(sprintf(
                    'Passed not callable function for event `%s` [%s:%s]',
                    $sEventName,
                    $aHandler[Vars::EVENT_CLASS],
                    $aHandler[Vars::EVENT_METHOD]
                ));
            }

            // проверяем наличие и адекватность имени класса, который прослушивается
            if (isset($aHandler[Vars::EVENT_TO_CLASS]) and !class_exists($aHandler[Vars::EVENT_TO_CLASS])) {
                throw new Exception('event listens to not existing class `' . $aHandler[Vars::EVENT_TO_CLASS] . '`');
            }
            // добавляем инфирмацию по устанавливающему событие модулю
            $aHandler[Vars::MODULE_NAME] = $oModuleConfig->getName();
            $aHandler[Vars::LAYER_NAME] = $oModuleConfig->getLayer();

            // добавляем в список событий
            $this->append([Vars::EVENTS, $sEventName], $aHandler);

            ++$iCnt;
        }

        return $iCnt;
    }

    /**
     * Удаляет набор событий указанного модуля
     * #event_test проверить тестом удаление элемента из середины, а то у меня сомнения по поводу последовательного уделения 2 и 4 элементов из 5.
     *
     * @param ModuleConfig $oModuleConfig
     */
    private function clearEvents(ModuleConfig $oModuleConfig)
    {
        $aAllEvents = $this->get(Vars::EVENTS);

        if (!$aAllEvents) {
            return;
        }

        foreach ($aAllEvents as $aEventName => $aEventList) {
            // флаг того, что в список обработчиков внесено изменение
            $bModified = false;

            foreach ($aEventList as $iKey => $aEvent) {
                // если модуль и слой совпадают
                if ($aEvent[Vars::MODULE_NAME] === $oModuleConfig->getName() &&
                    $aEvent[Vars::LAYER_NAME] === $oModuleConfig->getLayer()) {
                    // удаляем событие
                    unset($aEventList[$iKey]);
                    $bModified = true;
                }

                // если изменено, то сохранить новое значение
                if ($bModified) {
                    $aPath = [Vars::EVENTS, $aEventName];

                    if ($aEventList) {
                        $this->set($aPath, array_values($aEventList));
                    } else {
                        $this->remove($aPath);
                    }
                }
            }
        }
    }

    /**
     * Добавляет набор функциональных политик.
     *
     * @param ModuleConfig $oModuleConfig
     */
    private function addPolicyList(ModuleConfig $oModuleConfig)
    {
        $aList = $oModuleConfig->getVal(Vars::POLICY);

        if (is_array($aList)) {
            foreach ($aList as $aVal) {
                $this->addModulePolicy($oModuleConfig, $aVal);
            }
        }
    }

    /**
     * Добавляет запись политики.
     *
     * @param ModuleConfig $oModuleConfig
     * @param $aPolicyData
     *
     * @throws Exception
     */
    private function addModulePolicy(ModuleConfig $oModuleConfig, $aPolicyData)
    {
        if (!isset($aPolicyData[Vars::POLICY_VAL_NAME])) {
            throw new Exception('No `name` val in policy data');
        }
        if (!isset($aPolicyData[Vars::POLICY_VAL_DEFAULT])) {
            throw new Exception('No `default` val in policy data');
        }
        if (!$this->exists([Vars::POLICY, $oModuleConfig->getName()])) {
            $this->set([
                Vars::POLICY,
                $oModuleConfig->getLayer(),
                $oModuleConfig->getName(),
                Vars::POLICY_TITLE,
            ], $oModuleConfig->getTitle());
        }

        $aPath = [
            Vars::POLICY,
            $oModuleConfig->getLayer(),
            $oModuleConfig->getName(),
            Vars::POLICY_ITEMS,
        ];

        $this->append($aPath, $aPolicyData);
    }

    /**
     * Удаляет набор функциональных политик модуля.
     *
     * @param $oModuleConfig
     */
    private function clearPolicy(ModuleConfig $oModuleConfig)
    {
        $this->remove([
            Vars::POLICY,
            $oModuleConfig->getLayer(),
            $oModuleConfig->getName(),
        ]);
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
     * Отдает флаг наличия модуля.
     *
     * @param string $sModule имя модуля
     * @param string $sLayer имя слоя
     *
     * @return bool
     */
    public function moduleExists($sModule, $sLayer)
    {
        return $this->exists($this->getModulePath($sModule, $sLayer));
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
     * Удаляет модуль.
     *
     * @param string $sModule имя модуля
     * @param string $sLayer имя слоя
     *
     * @return bool
     */
    public function removeModule($sModule, $sLayer)
    {
        if (!$this->moduleExists($sModule, $sLayer)) {
            return false;
        }

        $oModuleConfig = $this->getModuleConfig($sModule, $sLayer);

        $this->remove($this->getModulePath($sModule, $sLayer));

        $this->clearEvents($oModuleConfig);
        $this->clearPolicy($oModuleConfig);

        return true;
    }

    public function addLayer($layerName)
    {
        if (!$this->get(Vars::LAYERS . $this->getDelimiter() . $layerName)) {
            $this->set(Vars::LAYERS . $this->getDelimiter() . $layerName, []);
        }
    }
}
