<?php

namespace skewer\build\Design\Tabs;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\base\site_module\Context;
use skewer\build\Cms;

/**
 * Модуль для вывода нескольких интерфейсов в дизайнерском режиме
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /**
     * Первичная инициализация.
     *
     * @return int
     */
    public function actionInit()
    {
        $this->addChildProcess(new Context('param_panel', 'skewer\build\Design\ParamPanel\Module', ctModule, []));
        $this->addChildProcess(new Context('templates_panel', 'skewer\build\Design\Inheritance\Module', ctModule, []));
        $this->addChildProcess(new Context('css_panel', 'skewer\build\Design\CSSEditor\Module', ctModule, []));
        $this->addChildProcess(new Context('zone_panel', 'skewer\build\Design\Zones\Module', ctModule, []));
        $this->addChildProcess(new Context('css_transfer', 'skewer\build\Design\CSSTransfer\Module', ctModule, []));
        $this->addChildProcess(new Context('settings', 'skewer\build\Design\Settings\Module', ctModule, []));

        return psComplete;
    }

    /**
     * Добавляет модуль.
     */
    protected function actionAddModule()
    {
        $sLabelName = $this->getStr('labelName');
        $iSectionId = $this->getInt('sectionId', \Yii::$app->sections->main());

        // запросить параметр в метке с админкой
        $aParam = Parameters::getByName($iSectionId, $sLabelName, Parameters::objectAdm, true);

        // если параметр есть
        if ($aParam) {
            // добавление модуля
            $this->addModule($sLabelName, $aParam['value'], $iSectionId);

            return;
        }

        // запросить параметр в обычного объекта
        $aParam = Parameters::getByName($iSectionId, $sLabelName, Parameters::object, true);
        if ($aParam) {
            // попробовать скомпоновать админское имя
            $sModuleName = preg_replace('/PageModule$/', 'AdmModule', $aParam['value']);

            if ($this->hasAdmModuleInSection($sModuleName, $iSectionId)) {
                $this->addModule($sModuleName, $sModuleName, $iSectionId);

                return;
            }
        }

        $this->addError('Модуль управления не найден');
    }

    /**
     * Добавляет модуль как подчиненный.
     *
     * @param $sLabelName
     * @param $sModuleName
     * @param $iSectionId
     */
    protected function addModule($sLabelName, $sModuleName, $iSectionId)
    {
        $sObjName = 'module_' . $sLabelName;

        if (!class_exists($sModuleName)) {
            $sModuleName = \skewer\base\site_module\Module::getClassOrExcept($sModuleName, Layer::ADM);
        }

        // создать
        $this->addChildProcess(new Context($sObjName, $sModuleName, ctModule, [
            'sectionId' => $iSectionId,
        ]));

        // добавить параметр "id раздела"
        $process = $this->getChildProcess($sObjName);

        // инструкции для интерфейсной части
        $this->setCmd('load_module');
        $this->setData('tabPath', $process->getLabelPath());
    }

    /**
     * Удаляет набор модулей.
     */
    protected function actionDelModule()
    {
        // набор путей для удаления
        $aList = $this->get('items');
        if (!is_array($aList)) {
            throw new \Exception('Неверный формат посылки');
        }
        $sCurrentPath = $this->oContext->oProcess->getLabelPath();

        // перебираем все
        foreach ($aList as $sPath) {
            // процесс должен быть подчиненным
            if (mb_strpos($sPath, $sCurrentPath . '.') !== 0) {
                continue;
            }

            $sLabel = mb_substr($sPath, mb_strlen($sCurrentPath) + 1);

            $this->removeChildProcess($sLabel);
        }

        // инструкции для интерфейсной части
        $this->setCmd('close_module');
        $this->setData('list', $aList);
    }

    /**
     * Определяет подключен ли модуль в разделе.
     *
     * @param string $sModuleName
     * @param int $iSectionId
     *
     * @return bool
     */
    private function hasAdmModuleInSection($sModuleName, $iSectionId)
    {
        $aParamList = Parameters::getList($iSectionId)
            ->fields(['name', 'value'])
            ->name(Parameters::objectAdm)
            ->asArray()
            ->rec()
            ->get();

        foreach ($aParamList as $aParam) {
            if ($aParam['value'] === $sModuleName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Добавление редактора.
     */
    protected function actionAddEditor()
    {
        $sEditorId = $this->getStr('editorId');
        $iSectionId = $this->getInt('sectionId', \Yii::$app->sections->main());

        $sObjName = 'editor_' . str_replace(['/', '.'], '_', $sEditorId);

        $lang = Parameters::getLanguage($iSectionId);
        if ($lang) {
            \Yii::$app->language = $lang;
        }

        // создать
        $this->addChildProcess(new Context($sObjName, 'skewer\build\Adm\Editor\SimpleModule', ctModule, [
            'sectionId' => $iSectionId,
        ]));

        // добавить параметр "id раздела"
        $process = $this->getChildProcess($sObjName);
        $process->addRequest('sectionId', $iSectionId);
        $process->addRequest('editorId', $sEditorId);
        $process->addRequest('closable', true);

        // инструкции для интерфейсной части
        $this->setCmd('load_module');
        $this->setData('tabPath', $process->getLabelPath());
    }
}
