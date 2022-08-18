<?php

namespace skewer\build\Cms\LeftPanel;

use skewer\base\site;
use skewer\base\site\Layer;
use skewer\base\site_module\Context;
use skewer\build\Cms;
use skewer\components\auth\CurrentAdmin;
use skewer\components\config\installer\Api as InstallerApi;

/**
 * Левая панель для основного админского интерфейса.
 *
 * @class: Module
 *
 * @Author: sapozhkov, $Author$
 * @version: $Revision$
 * @date: $Date$
 */
class Module extends Cms\Frame\ModulePrototype
{
    /**
     * Отдает конфигурацию модулей.
     *
     * @return string[]
     */
    protected function getModuleSet()
    {
        $aOut = [];

        if (CurrentAdmin::canRead(\Yii::$app->sections->root())) {
            $aOut['section'] = 'skewer\\build\\Adm\\Tree\\MainModule';
        }

        if (CurrentAdmin::isSystemMode()) {
            $aOut['tpl'] = 'skewer\\build\\Adm\\Tree\\TplModule';
        }

        if (CurrentAdmin::canRead(\Yii::$app->sections->library())) {
            $aOut['lib'] = 'skewer\\build\\Adm\\Tree\\LibModule';
        }

        if (site\Type::hasCatalogModule() && (CurrentAdmin::isSystemMode() || CurrentAdmin::canDo('skewer\\build\\Catalog\\LeftList\\Module', 'useCatalog'))) {
            $aOut['catalog'] = 'skewer\build\Catalog\LeftList\Module';
        }

        if (CurrentAdmin::isSystemMode() or CurrentAdmin::canDo('skewer\\build\\Tool\\Policy\\Module', 'useControlPanel')) {
            $aOut['tools'] = 'skewer\build\Tool\LeftList\Module';
        }

        $api = new InstallerApi();
        if (CurrentAdmin::isSystemMode() && $api->isInstalled('Testing', Layer::ADM)) {
            $aOut['testing'] = 'skewer\\build\\Adm\\Testing\\Module';
        }

        if (!$aOut) {
            $this->setData('error', 'Нет разрешений на доступ к разделам. Обратитесь к администратору');
        }

        return $aOut;
    }

    /**
     * Отдает объект модуля, который может работать с набором вкладок.
     *
     * @static
     *
     * @param $sLabel
     *
     * @throws \Exception
     *
     * @return Cms\LeftPanel\ModulePrototype
     */
    public function getModule($sLabel)
    {
        // проверить наличие модуля в конфигурации
        $aModuleSet = $this->getModuleSet();
        if (!isset($aModuleSet[$sLabel])) {
            throw new \Exception("Запрашиваемый модуль `{$sLabel}` набора вкладок не найден.");
        }
        /** @var \skewer\base\site_module\Process $oProcess */
        $oProcess = $this->getChildProcess($sLabel);
        if (!is_object($oProcess)) {
            throw new \Exception("Запрашиваемый модуль `{$sLabel}` набора вкладок не инициализирован.");
        }
        $oListModule = $oProcess->getModule();

        // проверить его принадлежность суперклассу
        if (!$oListModule instanceof Cms\LeftPanel\ModulePrototype) {
            throw new \Exception('Модуль набора вкладок не принадлежит требуемому суперклассу.');
        }

        return $oListModule;
    }

    /**
     * Инициализация набора элементов.
     *
     * @param string $defAction
     * @return int
     */
    public function execute($defAction = '')
    {
        $this->addInitParam(
            'lang',
            [
                'logPanelHeader' => \Yii::t('Forms', 'logPanelHeader'),
                'leftPanelTitle' => \Yii::t('Forms', 'leftPanelTitle'),
            ]
        );
        // добавление инициализированных модулей
        foreach ($this->getModuleSet() as $sAlias => $sModuleName) {
            $this->addSubPanel($sAlias, $sModuleName);
        }

        return psComplete;
    }

    // func

    /**
     * Добавление подчиненного элемента.
     *
     * @param $sAlias - псевдоним для составления пути
     * @param $sModuleName - имя модуля
     */
    protected function addSubPanel($sAlias, $sModuleName)
    {
        $aParams = [];

        $this->addChildProcess(new Context($sAlias, $sModuleName, ctModule, $aParams));
    }
}
