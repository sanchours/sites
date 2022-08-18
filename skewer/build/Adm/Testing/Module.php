<?php

namespace skewer\build\Adm\Testing;

use skewer\build\Adm\Testing\components\Helper;
use skewer\build\Cms;

/**
 * Class Module.
 */
class Module extends Cms\LeftPanel\ModulePrototype
{
    /** @var null|Service $service */
    private $service;

    protected $idTestObject;

    protected $shortPathTestObject;

    private $rootSection = 'root';

    /** @var string заместитель основной JS библиотеки */
    protected $sMainJSClass = '';

    /**
     * Отдает инициализационный массив для набора вкладок.
     *
     * @param int|string $idStructure идентификатор записи
     *
     * @return string[]
     */
    public function getTabsInitList($idStructure)
    {
        if ($idStructure) {
            $idStructure = Helper::getShortPathById($idStructure);
            $this->service = new Service($idStructure);

            return $this->service->getModuleForTabInit();
        }

        return [];
    }

    public function getTabsAddParams($path)
    {
        if ($this->service === null) {
            return [];
        }

        return $this->service->getParamsModule();
    }

    public function init()
    {
        parent::init();

        // если задан заместитель основной JS библиотеки
        if ($this->sMainJSClass) {
            // изменение стандартного имени модуля
            $this->setJSONHeader('externalLib', $this->sMainJSClass);

            // подцепить основной модуль как подчиненный
            $this->addLibClass('Testing', 'Adm', 'Testing');
        }

        $this->idTestObject = $this->getStr('id');
        $this->shortPathTestObject = Helper::getShortPathById($this->idTestObject);
    }

    /**
     * @throws \Exception
     * Состояние. Выбор корневого набора папок
     *
     * @return bool
     */
    protected function actionInit()
    {
        // загрузка элементов
        $this->setData('items', array_values(Helper::getRootStructure()));
        $this->setData('cmd', 'loadItems');

        // установка корневого раздела
        $this->addInitParam('rootSection', $this->rootSection);

        // название
        $this->addInitParam('title', \Yii::t('testing', 'testing'));

        return true;
    }

    /**
     * Раскрытие списка.
     *
     * @throws \skewer\base\ft\Exception
     *
     * @return bool
     */
    protected function actionGetSubItems()
    {
        $path = Helper::getPathTestSuiteObject($this->shortPathTestObject);
        $items = Helper::getStructTestCases($path);
        $this->setData('items', $items);

        $this->setData('cmd', 'loadItems');

        return true;
    }

    protected function actionSelectNode()
    {
        if (Helper::isTestSuiteFormat($this->idTestObject)) {
            $path = Helper::getPathTestSuiteObject($this->shortPathTestObject);
            $this->setData('testSuite', Helper::getDescriptionTestSuit($path));
        }

        $this->setData('cmd', 'selectNode');

        return true;
    }

    /**
     * @throws \Exception
     * Состояние. Выбор корневого набора разделов
     *
     * @return bool
     */
    protected function actionReloadTree()
    {
        $pathByDir = $this->shortPathTestObject;
        if (Helper::isTestSuiteFormat($this->shortPathTestObject)) {
            $this->setData(
                'path',
                Helper::getPathTestSuiteObject($this->shortPathTestObject)
            );
            $pathByDir = mb_substr($this->shortPathTestObject, 0, mb_strrpos($this->shortPathTestObject, '/'));
        }

        $files = Helper::getTreeByChildrenId($pathByDir);

        $this->setData('items', $files);
        $this->setData('dropAll', true);
        $this->setData('sectionId', $this->idTestObject);
        $this->setCmd('loadTree');

        return psComplete;
    }

    /**
     * Возвращает в поток дерево разделов, открытое до определенной вершины.
     *
     * @throws \skewer\base\ft\Exception
     */
    protected function actionGetTree()
    {
        // целевой раздел
        $pathByDir = $this->shortPathTestObject;

        if (Helper::isTestSuiteFormat($this->idTestObject)) {
            $this->setData(
                'path',
                Helper::getPathTestSuiteObject($this->shortPathTestObject)
            );
            $pathByDir = mb_substr($this->shortPathTestObject, 0, mb_strrpos($this->shortPathTestObject, '/'));
        }

        $files = Helper::getTreeByChildrenId($pathByDir);

        // отдать в вывод, если найдено
        if ($files) {
            $this->setData('items', $files);
            $this->setData('sectionId', $this->idTestObject);
            $this->setCmd('loadTree');
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     *
     * @return bool
     */
    protected function actionCreateScriptRunAll()
    {
        $script = \Yii::$app->view->renderPhpFile(
            __DIR__ . '/templates/testAll.php',
            ['pathToSite' => ROOTPATH]
        );

        $nameScript = 'testAll.sh';
        if (Helper::createFileForRun($nameScript, $script)) {
            $this->addMessage('Файл успешно создан', 'Его запуск происходит в ручном режиме');
        } else {
            throw new \Exception(\Yii::t('testing', 'not_create_executable_file'));
        }

        return true;
    }
}
