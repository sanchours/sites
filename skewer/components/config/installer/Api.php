<?php
/**
 * @class skewer\build\Component\Installer\Api
 *
 * @author ArmiT
 * @date 24.01.2014
 * @project Skewer
 */

namespace skewer\components\config\installer;

use skewer\base\command;
use skewer\base\log\Logger;
use skewer\base\site\HostTools;
use skewer\components\config;
use skewer\components\config\ConfigUpdater;
use skewer\components\i18n\Categories;
use skewer\helpers\Files;
use yii\base\UserException;

/**
 * API класс для работы с установкой.
 */
class Api
{
    /**
     * @const int INSTALLED Указывает на то, что модуль установлен. Используется как фильтр
     */
    const INSTALLED = 0x01;

    /**
     * @const int INSTALLED Указывает на то, что модуль не установлен. Используется как фильтр
     */
    const N_INSTALLED = 0x02;

    /**
     * @const int ALL Используется для указания на то, что флаг установки не важен
     */
    const ALL = 0x03;

    /**
     * Переустанавливает все зарегистрированные в реестре модули.
     *
     * @throws Exception
     */
    public function updateAllModulesConfig()
    {
        $aList = $this->getAllModuleList();

        try {
            $this->startDiagnosticMode();

            ConfigUpdater::init();

            ConfigUpdater::buildRegistry()->clear();

            $oHub = new command\Hub();

            // обновляем данные для модулей из списка
            foreach ($aList as &$installModule) {
                if (!$installModule instanceof Module) {
                    throw new Exception('Item must be an instance of Installer\\Module');
                }
                // стереть старый конфиг
                $oHub->addCommand(new system_action\uninstall\UnregisterConfig($installModule));

                // записать свежий
                $oHub->addCommand(new system_action\install\RegisterConfig($installModule));
            }

            $oHub->executeOrExcept();

            ConfigUpdater::commit();

            \Yii::$app->register->reloadData();
        } catch (\Throwable $e) {
            $this->stopDiagnosticMode();
            Logger::dumpException($e);
            throw new Exception('В процессе установки модуля произошли ошибки [' . $e->getMessage() . ']', $e->getCode(), $e);
        }

        $this->stopDiagnosticMode();
    }

    /**
     * Переустанавливает языковые метки для всех установленных модулей.
     *
     * @param bool $sResetBase флаг очистки базы
     *
     * @throws Exception
     */
    public function updateAllModulesLang($sResetBase = true)
    {
        $aList = $this->getAllModuleList();

        try {
            $this->startDiagnosticMode();

            $oHub = new command\Hub();

            if ($sResetBase) {
                $oHub->addCommand(new system_action\reinstall\ClearLanguage());
            }

            // обновляем данные для модулей из списка
            foreach ($aList as &$installModule) {
                if (!$installModule instanceof Module) {
                    throw new Exception('Item must be an instance of Installer\\Module');
                }
                // обновить языковые метки
                $oHub->addCommand(new system_action\reinstall\LanguageFile($installModule));
            }

            // обновить вынесенные языковые файлы
            foreach (Categories::$aAddLangList as $sName => $sPath) {
                $oHub->addCommand(new system_action\reinstall\ComponentLanguage($sName, $sPath));
            }

            // обновить вынесенные файлы данных
            foreach (Categories::$aAddPresetDataList as $sName => $sPath) {
                $oHub->addCommand(new system_action\reinstall\ComponentLanguage($sName, $sPath, true));
            }

            $oHub->executeOrExcept();

            \Yii::$app->getI18n()->clearCache();
        } catch (\Exception $e) {
            $this->stopDiagnosticMode();
            Logger::dumpException($e);
            throw new Exception('В процессе обновления словарей произошла ошибка [' . $e->getMessage() . ']', $e->getCode(), $e);
        }

        $this->stopDiagnosticMode();
    }

    /**
     * Выдает массив объектов всех модулей, установденных в системе.
     *
     * @return Module[]
     */
    private function getAllModuleList()
    {
        $aList = [];

        $layersList = \Yii::$app->register->getLayerList();
        foreach ($layersList as $layer) {
            $modules = $this->getInstalledModules($layer);
            foreach ($modules as $module) {
                $aList[] = $module;
            }
        }

        return $aList;
    }

    /**
     * Устанавливает модуль с именем $moduleName в слое $layer, учитывая зависимости.
     *
     * @param string $moduleName
     * @param string $layer
     *
     * @throws Exception
     *
     * @return array Возвращает массив объектов хранения данных по модулям
     */
    public function install($moduleName, $layer)
    {
        try {
            $this->startDiagnosticMode();

            ConfigUpdater::init();

            # получить данные корневого устанавливаемого модуля
            $module = IntegrityManager::init($moduleName, $layer);

            if ($module->alreadyInstalled) {
                throw new Exception(sprintf('Module [%s:%s] already installed', $module->moduleName, $module->layer));
            }
            # получить дерево зависимостей
            $installTree = $this->getInstallTree($module);

            if (!count($installTree)) {
                throw new Exception('All modules already installed');
            }
            $oHub = new command\Hub();
            foreach ($installTree as &$installModule) {
                $oHub->addCommand(new InstallModule($installModule));
            }

            $oHub->executeOrExcept();

            ConfigUpdater::commit();
        } catch (\Exception $e) {
            $this->stopDiagnosticMode();
            Logger::dumpException($e);
            throw new Exception(
                'В процессе установки модуля произошли ошибки [' . $e->getMessage() . ']',
                $e->getCode(),
                $e
            );
        }

        $this->stopDiagnosticMode();

        return $installTree;
    }

    /**
     * Удаляет модуль с именем $moduleName в слое $layer без учета зависимостей.
     *
     * @param string $moduleName Имя модуля без учета мнемоники и слоя
     * @param string $layer Имя слоя
     *
     * @throws Exception В случае Ошибки выбрасывает исключение skewer\build\Component\Installer\Exception
     */
    public function uninstall($moduleName, $layer)
    {
        try {
            $this->startDiagnosticMode();

            ConfigUpdater::init();

            # получить данные корневого устанавливаемого модуля
            $module = IntegrityManager::init($moduleName, $layer);

            if (!$module->alreadyInstalled) {
                throw new Exception(sprintf('Module [%s:%s]  does not installed', $module->moduleName, $module->layer));
            }
            $oHub = new command\Hub();
            $oHub->addCommand(new UninstallModule($module));

            $oHub->executeOrExcept();

            ConfigUpdater::commit();

            $this->stopDiagnosticMode();
        } catch (\Exception $e) {
            $this->stopDiagnosticMode();

            Logger::dumpException($e);

            throw new Exception(
                'В процессе Удаления модуля произошли ошибки [' . $e->getMessage() . ']',
                0,
                $e
            );
        }
    }

    /**
     * Обновляет данные модуля в реестре по файлу конфигурации без выполнения операций установки и удаления самого
     * модуля.
     *
     * @param string $moduleName Имя модуля без учета слоя и мнемоники
     * @param string $layer Имя слоя
     *
     * @throws Exception
     */
    public function updateConfig($moduleName, $layer)
    {
        try {
            $module = IntegrityManager::init($moduleName, $layer);

            $this->startDiagnosticMode();
            ConfigUpdater::init();
            $oHub = new command\Hub();
            $oHub->addCommand(new system_action\reinstall\UpdateConfig($module));
            $oHub->executeOrExcept();
            ConfigUpdater::commit();
            $this->stopDiagnosticMode();
        } catch (\Exception $e) {
            $this->stopDiagnosticMode();

            throw new Exception(
                'В процессе Обновления файла конфигурации модуля произошли ошибки [' . $e->getMessage() . ']',
                0,
                $e
            );
        }
    }

    /**
     * Обновляет данные словарей для модуля с именем $moduleName в слое $layer.
     *
     * @param string $moduleName
     * @param string $layer
     * @param bool $updateCache Флаг, указывающий на необходимость перестройки файла кеша для словарей
     *
     * @throws Exception
     */
    public function updateLanguage($moduleName, $layer, $updateCache = false)
    {
        try {
            $module = IntegrityManager::init($moduleName, $layer);

            $this->startDiagnosticMode();
            $oHub = new command\Hub();
            $oHub->addCommand(new system_action\reinstall\UpdateLanguage($module, $updateCache));
            $oHub->executeOrExcept();
            $this->stopDiagnosticMode();
        } catch (\Exception $e) {
            $this->stopDiagnosticMode();

            throw new Exception(
                'В процессе Обновления словарей модуля произошли ошибки [' . $e->getMessage() . ']',
                0,
                $e
            );
        }
    }

    /**
     * Обновляет css параметры в базе для модуля.
     *
     * @param string $moduleName
     * @param string $layer
     *
     * @throws Exception
     */
    public function updateCss($moduleName, $layer)
    {
        try {
            $module = IntegrityManager::init($moduleName, $layer);

            $this->startDiagnosticMode();
            $oHub = new command\Hub();
            $oHub->addCommand(new system_action\install\RegisterCss($module));
            $oHub->executeOrExcept();
            $this->stopDiagnosticMode();
        } catch (\Exception $e) {
            $this->stopDiagnosticMode();

            throw new Exception(
                'В процессе Обновления css модуля произошли ошибки [' . $e->getMessage() . ']',
                0,
                $e
            );
        }
    }

    /**
     * Возвращает массив экземпляров Installer\Module, установленных в слое $layer.
     *
     * @param string $layer
     *
     * @throws Exception
     * @throws config\Exception
     *
     * @return Module[]
     */
    public function getInstalledModules($layer)
    {
        return $this->getModules($layer, self::INSTALLED);
    }

    /**
     * Возвращает массив экземпляров Installer\Module, установленных в слое $layer.
     *
     * @param string $layer
     *
     * @throws Exception
     * @throws config\Exception
     *
     * @return Module[]
     */
    public function getAvailableModules($layer)
    {
        return $this->getModules($layer, self::N_INSTALLED);
    }

    /**
     * Отдает список модулей для всех зарегистрированных модулей.
     */
    public static function getLayers()
    {
        return \Yii::$app->register->getLayerList();
    }

    /**
     * Возвращает список доступных к установке либо удалению модулей.
     *
     * @param $layer
     * @param int $moduleStatus Фильтр на список модулей. Взможно применение побитовых операций
     *
     * @throws \skewer\components\config\installer\Exception
     *
     * @return Module[]
     */
    public static function getModules($layer, $moduleStatus = self::N_INSTALLED)
    {
        $layers = \Yii::$app->register->getLayerList();

        if (!count($layers)) {
            throw new Exception('Application does not contain layers');
        }
        $layerPath = BUILDPATH . $layer . \DIRECTORY_SEPARATOR;

        if (!is_dir($layerPath)) {
            throw new Exception('Real path for layer [' . $layer . ':' . $layerPath . '] is not exist');
        }
        $buildModules = Files::getDirectoryContent($layerPath, false, Files::DIRS);

        $out = [];
        if ($buildModules) {
            foreach ($buildModules as $item) {
                $module = IntegrityManager::init($item, $layer);

                if ($module->alreadyInstalled && ($moduleStatus & self::INSTALLED)) {
                    $out[] = $module;
                    continue;
                }

                if (!$module->alreadyInstalled && ($moduleStatus & self::N_INSTALLED)) {
                    $out[] = $module;
                    continue;
                }

                if ($moduleStatus & (self::N_INSTALLED & self::INSTALLED)) {
                    $out[] = $module;
                    continue;
                }
            }
        }

        return $out;
    }

    /**
     * Возвращает список объектов модулей в порядке их установки с учетом дерева зависимостей.
     *
     * @param Module $module Класс описания модуля, Для которого требуется построить дерево зависимостей
     * @param bool $fullTree Если указан, то строиться полное дерево без учета ранее установленных модулей
     * @param array $aParents Список родительских модулей
     *
     * @return Module[]
     *
     * Внимание! Процесс выполнения модульных инструкций необратим с точки зрения компонента Installer.
     * Поэтому, все инструкции должны быть оформлены таким образом, чтобы в случае возникновения ошибки, они могли быть
     * отменены.
     */
    public function getInstallTree(Module $module, $fullTree = false, $aParents = [])
    {
        $aParents[$module->layer][$module->moduleName] = true;

        $dependency = $module->moduleConfig->getDependency();

        $installTree = [];

        foreach ($dependency as $item) {
            list($moduleName, $layer) = $item;

            if (isset($aParents[$layer][$moduleName])) {
                continue;
            }

            $item = IntegrityManager::init($moduleName, $layer);

            $subDependency = $this->getInstallTree($item, $fullTree, $aParents);

            if (count($subDependency)) {
                foreach ($subDependency as $depItem) {
                    if (!in_array($depItem, $installTree)) { //Фильтровать ранее добавленные модули
                        $installTree[] = $depItem;
                    }
                }
            }
        }

        if ($fullTree | !$module->alreadyInstalled) {
            $installTree[] = $module;
        }

        return $installTree;
    }

    /**
     * Запустить диагностический режим. Т.е. остановить все процессоры, кроме системных.
     *
     * @throws Exception
     */
    protected function startDiagnosticMode()
    {
        /* Пытаемся остановить процессоры. В случае если он уже остановлен завершаем работу */
        if (!HostTools::enableProcessor(false)) {
            throw new Exception('Application already in diagnostic mode. Try later');
        }
    }

    /**\
     * Остановить диагностический режим. Т.е. запустить все процессоры
     */
    protected function stopDiagnosticMode()
    {
        HostTools::enableProcessor(true);
    }

    /**
     * Проверяет установлен ли модуль в системе.
     *
     * @param string $moduleName
     * @param string $layer
     *
     * @return bool
     */
    public function isInstalled($moduleName, $layer)
    {
        return \Yii::$app->register->moduleExists($moduleName, $layer);
    }

    /**
     * Выполнит список комманд после установки модуля и его зависимостей.
     */
    public function afterInstall()
    {
        $aCommands = config\installer\system_action\install\ExecuteModuleInstructions::$aCommandsAfterInstall;
        $aCommands = array_unique($aCommands);

        foreach ($aCommands as $sCommand) {
            $aTmpCommand = explode(':', $sCommand);

            if (count($aTmpCommand) !== 2) {
                throw new UserException('Неверная комманда: ' . $sCommand);
            }
            if (class_exists($aTmpCommand[0]) && method_exists($aTmpCommand[0], $aTmpCommand[1])) {
                call_user_func_array([$aTmpCommand[0], $aTmpCommand[1]], []);
            }
        }
    }
}
