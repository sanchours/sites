<?php
/**
 * repository for module description.
 *
 * @class skewer\build\Component\Installer\Module
 *
 * @author ArmiT
 * @date 27.01.14
 * @project canape
 */

namespace skewer\components\config\installer;

use skewer\components\config;

class Module
{
    /**
     * Содержит имя модуля без учета пути, слоя и мнемоники.
     *
     * @var string
     */
    public $moduleName = '';

    /**
     * Содержит имя класса файла установки.
     *
     * @var string
     */
    public $installClass = '';

    /**
     * Содержит имя класса файла ассетов.
     *
     * @var string
     */
    public $assetClass = '';

    /**
     * Содержить путь к файлу установки.
     *
     * @var string
     */
    public $installFile = '';

    /**
     * Содержит путь к файлу конфигурации модуля.
     *
     * @var string
     */
    public $configFile = '';

    /**
     * Содержит путь к файлу словарей.
     *
     * @var string
     */
    public $languageFile = '';

    /**
     * Содержит путь к файлу предустановленных данных.
     *
     * @var string
     */
    public $presetDataFile = '';

    /**
     * Содержит категорию для языкового словаря.
     *
     * @var string
     */
    public $languageCategory = '';

    /**
     * Содержит экземпляр класса ModuleConfig для хранения конфигурации устанавливаемого модуля.
     *
     * @var config\ModuleConfig
     */
    public $moduleConfig;

    /**
     * Имя слоя модуля.
     *
     * @var string
     */
    public $layer = '';

    /**
     * Путь к директории модуля.
     *
     * @var string
     */
    public $moduleRootDir = '';

    /**
     * Список зависимостей (модулей) для текущего.
     *
     * @var array
     */
    public $dependencyList = [];

    /**
     * Флаг, указывающий на то, что модуль уже установлен в системе и в случае отката изменений его трогать нельзя.
     *
     * @var bool
     */
    public $alreadyInstalled = false;
}
