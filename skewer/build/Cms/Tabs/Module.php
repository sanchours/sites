<?php

namespace skewer\build\Cms\Tabs;

use skewer\base\section\Parameters;
use skewer\base\site_module;
use skewer\build\Cms;
use skewer\controllers\CmsPrototype;
use yii\web\ServerErrorHttpException;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /** @var string суперкласс для подчиненных элементов */
    private $sAllowedChildClass = 'skewer\build\Cms\Tabs\ModulePrototype';

    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов.
     *
     * @return string
     */
    protected function getAllowedChildClass()
    {
        return $this->sAllowedChildClass;
    }

    /**
     * Устанавливает суперкласс для подчиненных элементов.
     *
     * @param $sAllowedChildClassName
     */
    protected function setAllowedChildClass($sAllowedChildClassName)
    {
        $this->sAllowedChildClass = $sAllowedChildClassName;
    }

    /**
     * Состояние. Первичная агрузка.
     *
     * @return array
     */
    protected function actionInit()
    {
        $this->addInitParam('lang', ['tabsSectionTitlePrefix' => \Yii::t('forms', 'tabsSectionTitlePrefix')]);
    }

    /**
     * Загрузка вкладок для определенной страницы.
     *
     * @throws \Exception
     */
    protected function actionLoadTabs()
    {
        // запросить набор параметров
        $mItemId = $this->getStr('itemId');
        $sListLabel = $this->getStr('module');
        $sTabName = $this->getStr('tab');

        /** Язык вкладки = языку раздела */
        $sLang = Parameters::getLanguage($mItemId);
        if ($sLang) {
            \Yii::$app->language = $sLang;
        }

        /** @var site_module\Process $oListContainerProcess объект контейнера списочных элементов */
        $oListContainerProcess = $this->getProcess(CmsPrototype::labelOut . '.' . Cms\Layout\Module::labelLeft, psAll);
        if (!$oListContainerProcess instanceof site_module\Process) {
            throw new ServerErrorHttpException('Не найден процесс контейнера списков в дереве');
        }
        $oListContainer = $oListContainerProcess->getModule();
        if (!$oListContainer instanceof Cms\LeftPanel\Module) {
            throw new ServerErrorHttpException('Процесс контейнера списков имеет неверный родительский класс');
        }
        // проверить нальчие модуля
        $oListModule = $oListContainer->getModule($sListLabel);

        $this->setAllowedChildClass($oListModule->getAllowedChildClassForTab());

        // запросить набор вкладок
        $aSubModules = $oListModule->getTabsInitList($mItemId);
        foreach ($oListModule->getErrors() as $sError) {
            $this->addError($sError);
        }
        $oListModule->clearMessages();

        // передать обратно в модуль
        $this->setData('itemId', $mItemId);
        $this->setData('module', $sListLabel);
        $this->setData('tab', $sTabName);

        // дополнительные параметры
        $aAddParams = $oListModule->getTabsAddParams($mItemId);

        // удалние всех подчиненных модулей
        $this->removeAllChildProcess();

        foreach ($aSubModules as $sAlias => $sSubModuleName) {
            // параметры
            $aParams = $aAddParams[$sAlias] ?? [];

            // добавление процесса
            $this->setProcess($sListLabel . '_' . $sAlias, $sSubModuleName, $sTabName, $aParams);
        }

        \Yii::$app->on(site_module\ProcessList::EVENT_AFTER_COMPLETE, [$this, 'afterCompletedProcessList']);
    }

    public function afterCompletedProcessList()
    {
        $childList = [];
        foreach ($this->oContext->oProcess->processes as $sName => $oProcess) {
            if ($oProcess->getStatus() === psComplete) {
                $childList[] = $sName;
            }
        }
        // передача списка имен вызванных объектов
        $this->setData('children', $childList);
    }

    /**
     * Создает/переустанавливает подчиненный объект
     *
     * @param string $sLabel Метка вызова
     * @param string $sClassName Имя класса вызываемого модуля
     * @param string $sSelectedTab имя текущей вкладки
     * @param array $aParams Параметры вызова модуля
     *
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    protected function setProcess($sLabel, $sClassName, $sSelectedTab, $aParams = [])
    {
        $process = $this->getChildProcess($sLabel);

        // если есть объект
        if ($process) {
            // установить статус новый
            $this->setChildProcessStatus($sLabel, psNew);
        } else {
            // нет - создать
            $process = $this->addChildProcess(new site_module\Context($sLabel, $sClassName, ctModule, $aParams));
            if (!$process) {
                throw new ServerErrorHttpException(sprintf('Не найден модуль [%s]', $sClassName));
            }
        }

        // для активной вкладки основной интерфейс сразу
        if ($sLabel === $sSelectedTab) {
            $process->addRequest('cmd', 'init');
        } else {
            // для остальных проверить возможность загрузки упрощенного

            // задать базовые параметры
            $oRC = new \ReflectionClass($process->getModuleClass());
            if ($oRC->isSubclassOf('skewer\build\Cms\Tabs\ModulePrototype')) {
                $process->addRequest('cmd', 'initTab');
            } else {
                $process->addRequest('cmd', 'init');
            }
        }

        $process->addRequest('itemId', $this->getInt('itemId'));
        $process->addRequest('sectionId', $this->getInt('itemId'));
    }
}// class
