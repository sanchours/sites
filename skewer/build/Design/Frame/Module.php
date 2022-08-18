<?php

namespace skewer\build\Design\Frame;

use skewer\base\section\Parameters;
use skewer\base\site_module;
use skewer\components\auth\CurrentAdmin;
use skewer\components\design\Design;
use skewer\components\design\DesignManager;
use skewer\components\ext;

/**
 * Class Module.
 */
class Module extends site_module\Prototype
{
    public function init()
    {
        $this->setParser(parserPHP);
    }

    // func

    public function execute()
    {
        // установка директории для шаблонов
        $this->setData('lang', ucfirst(\Yii::$app->i18n->getTranslateLanguage()));
        $this->setData('moduleDir', $this->getModuleWebDir());
        $this->setData('ver', Design::getLastUpdatedTime());

        // проверка прав доступа к дизайнерскому режиму
        if (!CurrentAdmin::allowDesignAccess()) {
            $this->setTemplate('denied.twig');

            return psComplete;
        }

        switch ((string) $this->getStr('mode')) {
            // страница загрузки
            case 'loading':
                $this->setTemplate('loading.php');

                break;

            // панель редактора
            case 'editor':

                $oProcessSession = new site_module\ProcessSession();
                $sTicket = $oProcessSession->createSession();

                $this->setData('sessionId', $sTicket);
                $this->setData('dictVals', json_encode($this->getDictVals()));
                $this->setTemplate('editor.php');

                break;

            // запрос пунктов контекстного меню
            case 'menu':

                // Реинициализация ExtJS - если были сброшены assets, то восстанавливаются
                ext\Api::init();

                // сборка меню
                $aMenu = [];
                $sVersion = $this->getStr('version', 'default');
                $iSectionId = $this->getInt('sectionId');
                $aGroupList = DesignManager::getGroupList($sVersion, ['id', 'name', 'title']);
                foreach ($aGroupList as $aGroup) {
                    $aMenu[$aGroup['name']] = [
                        'id' => $aGroup['id'],
                        'title' => $aGroup['title'],
                    ];
                }

                $this->setData(
                    'data',
                    json_encode([
                        'menu' => $aMenu,
                        'modules' => $this->getModuleTitltes($iSectionId),
                    ])
                );
                $this->setTemplate('blank.php');

                return psComplete;

                break;

            // окно с 2 панелями: отображения и редактора
            default:

                $this->setTemplate('index.php');

                break;
        }

        // глобальных флаг активации дизайнерского режима
        Design::setModeGlobalFlag();

        return psComplete;
    }

    // func

    /**
     * Отдает массив названий админских модулей для раздела
     * В качестве ключей идут метки.
     *
     * @param $iSectionId
     *
     * @return array
     */
    protected function getModuleTitltes($iSectionId)
    {
        $aOut = [];

        $aParamList = Parameters::getList($iSectionId)
            ->fields(['name', 'value'])
            ->asArray()
            ->name(Parameters::object)
            ->rec()
            ->asArray()
            ->get();

        foreach ($aParamList as $aParam) {
            if ($aParam['value']) {
                $aOut[$aParam['group']] = Api::getModuleTitleByName($aParam['value']);
            }
        }

        return $aOut;
    }

    /**
     * Отдает набор языковых метод для работы интерфейса.
     *
     * @return array()
     */
    private function getDictVals()
    {
        return $this->parseLangVars([
            'fileBrowserSelect',
            'fileBrowserFile',
            'galleryBrowserSelect',
            'delRowHeader',
            'delRow',
            'delRowNoName',
            'allowDoHeader',
            'confirmHeader',
            'clear',
            'start',
            'end',
            'editorCloseConfirmHeader',
            'editorCloseConfirm',
            'error',
            'ajax_error',
        ]);
    }

    // func
}// class
