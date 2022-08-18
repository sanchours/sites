<?php
/**
 * Менеджер проверки целостности и состояния файлов модуля.
 *
 * @author ArmiT
 * @date 03.02.14
 * @project canape
 */

namespace skewer\components\config\installer;

use skewer\components\config;

class IntegrityManager
{
    /**
     * @const int MODULE_NAMESPACE Паттерн для сборки пространства имен
     */
    const MODULE_NAMESPACE = 'skewer\\build\\%s\\%s';

    /**
     * @const int MODULE_INSTALL Паттерн сборки имени файла установки
     */
    const MODULE_INSTALL = '%s%sInstall';

    /**
     * @const int MODULE_ASSET Паттерн сборки имени файла
     */
    const MODULE_ASSET = '%s%sAsset';

    /**
     * @const int MODULE_CONFIG Паттерн сборки имени файла конфигурации
     */
    const MODULE_CONFIG = '%s%sConfig.php';

    /**
     * @const int MODULE_LANG Паттерн сборки имени файла словарей
     */
    const MODULE_LANG = '%s%sLanguage.php';

    /**
     * @const int MODULE_PRESETDATA Паттерн сборки имени файла предустановленных данных
     */
    const MODULE_PRESETDATA = '%s%sPresetData.php';

    /**
     * Инициализирует класс проверки целостности данных модулю. Производит проверку на наличие валидных входных данных,
     * наличие файла установки, конфигурации и исполняемого файла модуля.
     *
     * @param $moduleName
     * @param $layer
     *
     * @throws Exception
     *
     * @return Module Возвращает экземпляр Класса Installer\Module
     */
    public static function init($moduleName, $layer)
    {
        if (empty($moduleName)) {
            throw new Exception('Name for module is empty');
        }
        if (empty($layer)) {
            throw new Exception('Layer for module is empty');
        }
        $module = new Module();

        $module->moduleName = $moduleName;
        $module->layer = $layer;
        $exec = self::getModuleClass($moduleName, $layer);

        /*
         * Получить путь до корневой директории модуля
         */
        $modulePath = \Yii::getAlias('@' . str_replace('\\', '/', $exec) . '.php');
        if (!$modulePath) {
            throw new Exception('Module executor file[' . $exec . '] not found');
        }
        /* отрезаем имя исполняемого фалйла - получаем путь к корневому каталогу модуля в кластере */
        $module->moduleRootDir = dirname($modulePath) . \DIRECTORY_SEPARATOR;

        // Проверяем наличие файла конфигурации
        if (!file_exists($configFile = self::getConfigFile($module->moduleRootDir))) {
            throw new Exception('Config file [' . $configFile . '] for module [' . $moduleName . '] does not exist');
        }
        $module->configFile = $configFile;
        $module->moduleConfig = self::loadConfigFile($configFile);

        if (file_exists($langFile = self::getLangFile($module->moduleRootDir))) {
            $module->languageFile = $langFile;
        }

        if (file_exists($langFile = self::getPresetDataFile($module->moduleRootDir))) {
            $module->presetDataFile = $langFile;
        }

        /* Имя категории для словарей */
        $module->languageCategory = $module->moduleConfig->getLanguageCategory();

        /*
         * Проверить существование класса установки с пространством имен и без
         */

        if (!class_exists($install = self::getInstallClass($moduleName, $layer))) {
            throw new Exception('Installation file for module [' . $moduleName . '] does not exist');
        }
        $module->installClass = $install;

        $module->assetClass = self::getAssetClass($moduleName, $layer);

        $module->installFile;

        $module->alreadyInstalled = \Yii::$app->register->moduleExists($moduleName, $layer);

        return $module;
    }

    /**
     * Возвращает сформированное имя класса установки для модуля $moduleName в слое $layer.
     *
     * @param string $moduleName Имя модуля, путь к классу установки которого нужно получить
     * @param string $layer Имя слоя модуля
     *
     * @return string
     */
    protected static function getInstallClass($moduleName, $layer)
    {
        return sprintf(self::MODULE_NAMESPACE . '%s', $layer, $moduleName, '\Install');
    }

    /**
     * Возвращает сформированное имя класса ассета для модуля $moduleName в слое $layer.
     *
     * @param string $moduleName Имя модуля, путь к классу установки которого нужно получить
     * @param string $layer Имя слоя модуля
     *
     * @return string
     */
    protected static function getAssetClass($moduleName, $layer)
    {
        return sprintf(self::MODULE_NAMESPACE . '%s', $layer, $moduleName, '\Asset');
    }

    /**
     * Возвращает полное имя файла конфигурации модуля.
     *
     * @param string $moduleRootPath Путь к корневой директории модуля
     *
     * @return string
     */
    protected static function getConfigFile($moduleRootPath)
    {
        return $moduleRootPath . 'Config.php';
    }

    /**
     * Возвращает полное имя файла словарей модуля.
     *
     * @param string $moduleRootPath Путь к корневой директории модуля
     *
     * @return string
     */
    protected static function getLangFile($moduleRootPath)
    {
        return $moduleRootPath . 'Language.php';
    }

    /**
     * Возвращает полное имя файла предустановленных данных модуля.
     *
     * @param string $moduleRootPath Путь к корневой директории модуля
     *
     * @return string
     */
    protected static function getPresetDataFile($moduleRootPath)
    {
        return $moduleRootPath . 'PresetData.php';
    }

    /**
     * Проверяет возможность чтения файла конфигурации, расположенного по пути $moduleConfigPath.
     * В случае успешной проверки загружает его и создает экземляр класса Config\ModuleConfig.
     *
     * @param $moduleConfigPath
     *
     * @throws Exception В случае ошибки выбрасывает исключение skewer\build\Component\Installer\Exception либо
     * \Exception в зависимости от уровня произошедшей ошибки
     *
     * @return config\ModuleConfig Возвращает экземпляр класса хранения настроек модуля
     */
    protected static function loadConfigFile($moduleConfigPath)
    {
        if (!is_readable($moduleConfigPath)) {
            throw new Exception('Configuration file [' . $moduleConfigPath . '] is not readable');
        }
        $moduleConfig = new config\ModuleConfig(include($moduleConfigPath));

        return $moduleConfig;
    }

    /**
     * Возвращает предполагаемое имя основного исполняемого класса модуля.
     *
     * @param string $moduleName Имя модуля (без указания мнемоник и слоя)
     * @param string $layer Имя слоя
     *
     * @return string
     */
    protected static function getModuleClass($moduleName, $layer)
    {
        return sprintf(self::MODULE_NAMESPACE . '%s', $layer, $moduleName, '\Module');
    }
}
