<?php

namespace skewer\build\Tool\ModulesManager;

use skewer\base\site\Layer;
use skewer\base\ui;
use skewer\build\Tool;
use skewer\components\config\installer;
use skewer\components\ext;
use yii\helpers\ArrayHelper;

/**
 * Менеджер модулей.
 *
 * @class ModulesManager
 * @extends Tool\LeftList\ModulePrototype
 * @project Skewer
 *
 * @author ArmiT
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /** @var string Флаг показа всех модулей */
    const ALL_MODULES = 'All';

    /**
     * Метки статусов модулей для словарей.
     *
     * @var array
     */
    protected $modulesStatus = [
        installer\Api::INSTALLED => 'module_status_installed',
        installer\Api::N_INSTALLED => 'module_status_notinstalled',
    ];

    /**
     * Набор выводимых в списке полей.
     *
     * @return array
     */
    protected function getFieldList()
    {
        return [
            'moduleName' => [
                \Yii::t('modulesManager', 'field_module_name'),
                3,
            ],
            'definer' => [
                \Yii::t('modulesManager', 'field_layer') . '/' . \Yii::t('modulesManager', 'field_module_name'),
                2,
            ],
            'version' => [
                \Yii::t('modulesManager', 'field_version'),
                1,
            ],
            'status' => [
                \Yii::t('modulesManager', 'field_status'),
                2,
            ],
        ];
    }

    /**
     * фильтр слоя.
     *
     * @var string
     */
    protected $layerFilter = Layer::PAGE;

    /**
     * Фильтр статуса.
     *
     * @var int
     */
    protected $statusFilter = installer\Api::ALL;

    /**
     * Инстанс инсталлера.
     *
     * @var null|installer\Api
     */
    protected $installer;
    /** @var string Фильтр по названию и имени модуля */
    protected $nameModuleFilter = '';
    /** @var string Фильтр по слою / названию модуля */
    protected $nameLayerFilter = '';

    protected function preExecute()
    {
        $this->installer = new installer\Api();
        $sFilterSelectLayer = $this->get('layers_filter', $this->layerFilter);
        if ($sFilterSelectLayer === self::ALL_MODULES) {
            $this->layerFilter = $sFilterSelectLayer;
        } else {
            $this->layerFilter = Api::checkLayer($sFilterSelectLayer, $this->layerFilter);
        }
        $this->nameModuleFilter = trim($this->get('module_name', ''));
        $this->nameLayerFilter = trim($this->get('layer_name', ''));
        $this->statusFilter = (int) $this->get('status_filter', $this->statusFilter);
        $this->statusFilter = !$this->statusFilter ? (installer\Api::INSTALLED | installer\Api::N_INSTALLED) : $this->statusFilter;
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    // func

    protected function actionList()
    {
        $oList = new ext\ListView();

        /* Собираем список */
        foreach ($this->getFieldList() as $name => $field) {
            $oField = new ext\field\StringField();
            $oField->setName($name);
            $oField->setTitle($field[0]);
            $oField->setAddListDesc([
                'flex' => $field[1],
            ]);

            //$oField->setValue( $aItem[ $aType['val'] ] );
            $oList->addField($oField);
        }

        if ($this->layerFilter == self::ALL_MODULES) {
            $modules = [];
            foreach (Api::getLayers() as $key => $layer) {
                $modules = array_merge($modules, $this->installer->getModules($layer, $this->statusFilter));
            }
        } else {
            $modules = $this->installer->getModules($this->layerFilter, $this->statusFilter);
        }

        $items = [];

        foreach ($modules as $module) {
            //Профильтруем по названию модуля
            if ($this->nameModuleFilter) {
                $needle = mb_strtolower($this->nameModuleFilter);
                $sModuleTitle = $module->moduleConfig->getTitle();
                $sModuleTitle = mb_strtolower($sModuleTitle, 'UTF-8');
                if (mb_strpos($sModuleTitle, $needle, 0, 'UTF-8') === false) {
                    continue;
                }
            }

            //Профильтруем по слою и названию модуля
            if ($this->nameLayerFilter) {
                $needle = mb_strtolower($this->nameLayerFilter);
                $bHasIncoming = false;
                $layer = $moduleName = mb_strtolower($module->layer);
                $moduleName = mb_strtolower($module->moduleName);
                if (mb_strpos($layer, $needle, 0, 'UTF-8') !== false
                    or mb_strpos($moduleName, $needle, 0, 'UTF-8') !== false
                    or mb_strpos($layer . '/' . $moduleName, $needle, 0, 'UTF-8') !== false
                ) {
                    $bHasIncoming = true;
                }
                if (!$bHasIncoming) {
                    continue;
                }
            }

            $items[] = [
                'moduleName' => $module->moduleConfig->getTitle(),
                'definer' => $module->layer . ' / ' . $module->moduleName,
                'version' => $module->moduleConfig->getVersion(),
                'status' => $module->alreadyInstalled ?
                    \Yii::t('modulesManager', $this->modulesStatus[installer\Api::INSTALLED]) :
                    \Yii::t('modulesManager', $this->modulesStatus[installer\Api::N_INSTALLED]),
            ];
        }

        ArrayHelper::multisort($items, 'moduleName');

        $oList->setValues($items);

        /* Добавить фильтр по слоям */
        $filter = [];
        foreach (['ALL' => self::ALL_MODULES] + Api::getLayers() as $layer) {
            $filter[$layer] = \Yii::t('modulesManager', 'layer_name_' . $layer);
        }

        $oList->addFilterSelect('layers_filter', $filter, $this->layerFilter, \Yii::t('modulesManager', 'field_layer'), [
            'set' => true,
        ]);

        /* Добавить фильтр по статусу установки */
        $oList->addFilterSelect('status_filter', $this->getStatusList(), $this->statusFilter, \Yii::t('modulesManager', 'field_status'));
        $oList->addFilterText('module_name', $this->nameModuleFilter, \Yii::t('modulesManager', 'field_module_name'));
        $oList->addFilterText('layer_name', $this->nameLayerFilter, \Yii::t('modulesManager', 'field_layer') . ' / ' . \Yii::t('modulesManager', 'field_module_name'));

        $oList->allowSorting();

        $btn = new ui\element\RowButton();
        $btn->setTitle(\Yii::t('modulesManager', 'list_view_btn'));
        $btn->setIcon('icon-view');
        $btn->setPhpAction('View');
        $btn->setJsState('edit_form');
        $oList->addRowBtn($btn);

        $this->setInterface($oList);
    }

    protected function actionView()
    {
        $oForm = new ext\FormView();

        try {
            $data = $this->get('data');

            list($data['layer'], $data['moduleName']) = explode(' / ', $data['definer']);

            $module = installer\IntegrityManager::init($data['moduleName'], $data['layer']);

            /* Заголовочная часть */
            $oForm->setAddText(sprintf(
                '<h2>%s (%s/%s %s)</h2>',
                $module->moduleConfig->getTitle(),
                $module->moduleConfig->getLayer(),
                $module->moduleConfig->getName(),
                $module->moduleConfig->getVersion()
            ));

            /* Статус */
            $oField = new ext\field\Show();
            $oField->setName('status');
            $oField->setTitle(\Yii::t('modulesManager', 'field_status'));
            $oField->setValue(
                $module->alreadyInstalled ?
                    \Yii::t('modulesManager', 'module_status_installed') :
                    \Yii::t('modulesManager', 'module_status_notinstalled')
            );
            $oForm->addField($oField);

            /* Имя модуля */
            $oField = new ext\field\Show();
            $oField->setName('moduleName');
            $oField->setTitle('ID');
            $oField->setValue($module->moduleName);
            $oForm->addField($oField);

            /*Слой*/
            $oField = new ext\field\Show();
            $oField->setName('layer');
            $oField->setTitle(\Yii::t('modulesManager', 'field_layer'));
            $oField->setValue($module->layer);
            $oForm->addField($oField);

            /*Ревизия*/
            $oField = new ext\field\Show();
            $oField->setName('revision');
            $oField->setTitle(\Yii::t('modulesManager', 'field_version'));
            $oField->setValue($module->moduleConfig->getRevision());
            $oForm->addField($oField);

            /*Описание*/
            $oField = new ext\field\Show();
            $oField->setName('description');
            $oField->setTitle(\Yii::t('modulesManager', 'field_description'));
            $oField->setValue($module->moduleConfig->getDescription());
            $oForm->addField($oField);

            try {
                $dependencyTree = $this->installer->getInstallTree($module, true);
                if (count($dependencyTree)) {
                    array_splice($dependencyTree, -1, 1);
                } // удалить текущий модуль, дабы не вводить в заблуждение

                if (count($dependencyTree)) {
                    $oField = new ext\field\Show();
                    $oField->setName('delim');
                    $oField->setTitle(\Yii::t('modulesManager', 'deptree_delim'));
                    $oField->setValue('');
                    $oForm->addField($oField);
                }

                foreach ($dependencyTree as $item) {
                    $oField = new ext\field\Show();
                    $oField->setName('dependency_' . $item->moduleName . $item->layer);

                    $oField->setTitle(
                        sprintf(
                            '%s (%s/%s %s)',
                            $item->moduleConfig->getTitle(),
                            $item->moduleConfig->getLayer(),
                            $item->moduleConfig->getName(),
                            $item->moduleConfig->getVersion(),
                            $item->moduleConfig->getDescription()
                        )
                    );
                    $oField->setValue(
                        $item->alreadyInstalled ?
                            \Yii::t('modulesManager', 'module_status_installed') :
                            \Yii::t('modulesManager', 'module_status_notinstalled')
                    );

                    $oForm->addField($oField);
                }
            } catch (\Exception $e) {
                $oField = new ext\field\Show();
                $oField->setName('dependency_attention');

                $oField->setTitle(\Yii::t('modulesManager', 'wrong_dependency_header'));
                $oField->setValue(\Yii::t('modulesManager', 'wrong_dependency', $e->getMessage()));
                $oForm->addField($oField);
            }

            /* к списку */
            $oForm->addExtButton(
                ext\docked\Api::create(\Yii::t('modulesManager', 'tolist_btn'))
                    ->setIconCls(ext\docked\Api::iconCancel)
                    ->setAction('List')
                    ->unsetDirtyChecker()
            );

            if ($module->alreadyInstalled) {
                $oForm->addExtButton(
                    ext\docked\Api::create(\Yii::t('modulesManager', 'remove_btn'))
                        ->setIconCls(ext\docked\Api::iconDel)
                        ->setAction('remove')
                        ->setAddParamList([
                            'moduleName' => $module->moduleName,
                            'layer' => $module->layer,
                        ])
                        ->setConfirm(\Yii::t('modulesManager', 'remove_confirm'))
                );

                $oForm->addExtButton(
                    ext\docked\Api::create(\Yii::t('modulesManager', 'reinstall_btn'))
                        ->setIconCls(ext\docked\Api::iconReinstall)
                        ->setAction('reinstall')
                        ->setAddParamList([
                            'moduleName' => $module->moduleName,
                            'layer' => $module->layer,
                            'data' => [
                                'definer' => $module->layer . ' / ' . $module->moduleName,
                            ],
                        ])
                        ->setConfirm(\Yii::t('modulesManager', 'reinstall_confirm'))
                );

                $oForm->addExtButton(
                    ext\docked\Api::create(\Yii::t('modulesManager', 'updconf_btn'))
                        ->setIconCls(ext\docked\Api::iconConfiguration)
                        ->setAction('updateConfig')
                        ->setAddParamList([
                            'moduleName' => $module->moduleName,
                            'layer' => $module->layer,
                            'data' => [
                                'definer' => $module->layer . ' / ' . $module->moduleName,
                            ],
                        ])
                        ->setConfirm(\Yii::t('modulesManager', 'updconf_confirm'))
                );

                $oForm->addExtButton(
                    ext\docked\Api::create(\Yii::t('modulesManager', 'upddict_btn'))
                        ->setIconCls(ext\docked\Api::iconLanguages)
                        ->setAction('updateDictionary')
                        ->setAddParamList([
                            'moduleName' => $module->moduleName,
                            'layer' => $module->layer,
                            'data' => [
                                'definer' => $module->layer . ' / ' . $module->moduleName,
                            ],
                        ])
                        ->setConfirm(\Yii::t('modulesManager', 'upddict_confirm'))
                );

                $oForm->addExtButton(
                    ext\docked\Api::create(\Yii::t('modulesManager', 'upd_css_btn'))
                        ->setIconCls('icon-view')
                        ->setAction('updateCss')
                        ->setAddParamList([
                            'moduleName' => $module->moduleName,
                            'layer' => $module->layer,
                            'data' => [
                                'definer' => $module->layer . ' / ' . $module->moduleName,
                            ],
                        ])
                );
            } else {
                $oForm->addExtButton(
                    ext\docked\Api::create(\Yii::t('adm', 'save'))
                        ->setTitle(\Yii::t('modulesManager', 'install_btn'))
                        ->setIconCls(ext\docked\Api::iconInstall)
                        ->setAction('install')
                        ->setAddParamList([
                            'moduleName' => $module->moduleName,
                            'layer' => $module->layer,
                        ])
                        ->setConfirm(\Yii::t('modulesManager', 'install_confirm'))
                );
            }
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->setInterface($oForm);
    }

    protected function actionInstall()
    {
        $moduleName = $this->get('moduleName');
        $layer = $this->get('layer');

        try {
            $this->installer->install($moduleName, $layer);

            $this->installer->afterInstall();

            $this->addMessage(\Yii::t('modulesManager', 'install_successful', [$layer, $moduleName]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();
    }

    protected function actionRemove()
    {
        $moduleName = $this->get('moduleName');
        $layer = $this->get('layer');

        try {
            $this->installer->uninstall($moduleName, $layer);
            $this->addMessage(\Yii::t('modulesManager', 'remove_successful', [$layer, $moduleName]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();
    }

    protected function actionReinstall()
    {
        $moduleName = $this->get('moduleName');
        $layer = $this->get('layer');

        try {
            $this->installer->uninstall($moduleName, $layer);
            $this->installer->install($moduleName, $layer);
            $this->addMessage(\Yii::t('modulesManager', 'reinstalled_successful', [$layer, $moduleName]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionView();
    }

    protected function actionUpdateConfig()
    {
        $moduleName = $this->get('moduleName');
        $layer = $this->get('layer');

        try {
            $this->installer->updateConfig($moduleName, $layer);
            $this->addMessage(\Yii::t('modulesManager', 'updconf_successful', [$layer, $moduleName]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionView();
    }

    protected function actionUpdateDictionary()
    {
        $moduleName = $this->get('moduleName');
        $layer = $this->get('layer');

        try {
            $this->installer->updateLanguage($moduleName, $layer, true);
            $this->addMessage(\Yii::t('modulesManager', 'upddict_successful', [$layer, $moduleName]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionView();
    }

    protected function actionUpdateCss()
    {
        $moduleName = $this->get('moduleName');
        $layer = $this->get('layer');

        try {
            $this->installer->updateCss($moduleName, $layer);
            $this->addMessage(\Yii::t('modulesManager', 'upd_css_successful', [$layer, $moduleName]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionView();
    }

    /**
     * @return array
     */
    protected function getStatusList()
    {
        $aStatusList = [];
        foreach ($this->modulesStatus as $iStatusNum => $sStatus) {
            $aStatusList[$iStatusNum] = \Yii::t('modulesManager', $sStatus);
        }

        return $aStatusList;
    }
}
