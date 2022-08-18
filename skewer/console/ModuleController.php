<?php

namespace app\skewer\console;

use skewer\components\config\ConfigUpdater;
use skewer\components\config\installer;
use yii\helpers\Console;

/**
 * Класс для работы с установленными модулями системы.
 */
class ModuleController extends Prototype
{
    public $defaultAction = 'list';

    /**
     * Возвращает список модулей.
     *
     * @param string $layer Page / Cms / ...
     * @param string $filter new / installed / all
     *
     * @throws installer\Exception
     */
    public function actionList($layer = '', $filter = '')
    {
        // выбираем слой, если нужно
        if (!$layer) {
            // формируем список слоев
            $layerList = installer\Api::getLayers();
            $layerList = array_combine($layerList, $layerList);
            $layerList[''] = 'Page';

            // выбираем слой
            $layer = $this->select('Выберите слой [Page по умолчанию]:', $layerList);
            if (!$layer) {
                $layer = 'Page';
            }
        }

        // выбираем тип фильтрации
        if (!$filter) {
            $filter = $this->select('Какие модули показать [all]:', [
                'new' => 'Не установленные',
                'installed' => 'Установленные',
                'all' => 'Все',
                '' => 'Все',
            ]);

            if (!$filter) {
                $filter = 'all';
            }
        }

        switch ($filter) {
            case 'new':
                $filter = installer\Api::N_INSTALLED;
                break;

            case 'installed':
                $filter = installer\Api::INSTALLED;
                break;

            case 'all':
                $filter = installer\Api::INSTALLED | installer\Api::N_INSTALLED;
                break;

            default:
                $this->stderr("Неизвестный фильтр [{$filter}]");
        }

        $out = '';
        foreach (installer\Api::getModules($layer, $filter) as $module) {
            $this->stdout($module->alreadyInstalled ? '[inst] ' : '       ');
            $this->stdout($module->moduleName, Console::UNDERLINE);

            $this->stdout(' - ' . $module->moduleConfig->getDescription());

            $this->stdout("\r\n");
        }

        $this->stdout($out);
    }

    /**
     * Устанавливает модуль.
     *
     * @param string $moduleName имя модуля
     * @param string $layer имя слоя
     *
     * @throws installer\Exception
     */
    public function actionInstall($moduleName, $layer)
    {
        $api = new installer\Api();
        $tree = $api->install($moduleName, $layer);

        $this->stdout("Installation result:\r\n");

        /** @var installer\Module $item */
        foreach ($tree as $item) {
            $this->stdout($item->moduleName . "\r\n", Console::UNDERLINE);
        }
    }

    /**
     * Деинсталировать модуль.
     *
     * @param string $moduleName имя модуля
     * @param string $layer имя слоя
     *
     * @throws installer\Exception
     */
    public function actionUninstall($moduleName, $layer)
    {
        $api = new installer\Api();
        $api->uninstall($moduleName, $layer);

        $this->stdout(sprintf(
            "Module %s successfully uninstalled\r\n",
            $this->ansiFormat($moduleName, Console::UNDERLINE)
        ));
    }

    /**
     * Удаляет запись о модуле из реестра в обход всех проверок
     * Применяется если файлы модуля отсутствуют и не получается использовать uninstall.
     *
     * @param $moduleName
     * @param $layer
     */
    public function actionRemoveFromRegistry($moduleName, $layer)
    {
        ConfigUpdater::init();
        ConfigUpdater::buildRegistry()->removeModule($moduleName, $layer);
        ConfigUpdater::commit();
    }

    /**
     * Переустанавливает модуль.
     *
     * @param string $moduleName имя модуля
     * @param string $layer имя слоя
     *
     * @throws installer\Exception
     */
    public function actionReinstall($moduleName, $layer)
    {
        $this->actionUninstall($moduleName, $layer);
        $this->actionInstall($moduleName, $layer);
    }

    /**
     * Переустанавливает модуль.
     *
     * @param string $moduleName имя модуля
     * @param string $layer имя слоя
     *
     * @throws installer\Exception
     */
    public function actionUpdateModule($moduleName, $layer)
    {
        $api = new installer\Api();
        $api->updateConfig($moduleName, $layer);

        $this->stdout(sprintf(
            "Config file for module %s successfully updated \r\n",
            $this->ansiFormat($moduleName, Console::UNDERLINE)
        ));
    }
}
