<?php

namespace skewer\build\Adm\Testing;

use skewer\base\site_module\Context;
use skewer\build\Adm\Testing\components\Helper;
use skewer\build\Adm\Testing\view\BlockTests;
use skewer\build\Adm\Testing\view\Modules;
use yii\helpers\ArrayHelper;

class ModuleBlockTests extends AbstractModule
{
    /** @var ServiceBlockTests $service */
    protected $service;

    /**
     * ModuleBlockTests constructor.
     *
     * @param Context $oContext
     *
     * @throws \Exception
     */
    public function __construct(Context $oContext)
    {
        parent::__construct($oContext);

        $params = $oContext->getParams();
        $pathBlockTests = $params['pathObject'] ?? '';

        if (!$pathBlockTests) {
            throw new \Exception(\Yii::t('testing', 'not_block_tests'));
        }

        $this->service = new ServiceBlockTests($pathBlockTests);
    }

    /**
     * @throws \Exception
     */
    protected function actionInit()
    {
        $this->setPanelName($this->service->getTitleBlockTests());

        if ($this->service->hasTestSuites()) {
            return $this->actionShowTestSuitesModule();
        }

        return $this->actionShowModules();
    }

    /**
     * @throws \Exception
     */
    public function actionInitTab()
    {
        return $this->actionInit();
    }

    /**
     * Отображение доступных модулей тестирования.
     *
     * @throws \skewer\base\ft\Exception
     */
    private function actionShowModules()
    {
        return $this->render(new Modules([
            'modules' => $this->service->getTestsModules(),
        ]));
    }

    /**
     * Отображение доступных TestSuites Модуля.
     *
     * @throws \skewer\base\ft\Exception
     */
    private function actionShowTestSuitesModule()
    {
        return $this->render(new BlockTests([
            'testSuites' => $this->service->getTestSuitesModule(),
        ]));
    }

    public function actionChangeStateManualTest()
    {
        $idPath = $this->getStr('id');

        if (!$this->service->changeState($idPath)) {
            $this->addError(
                \Yii::t('testing', 'error'),
                \Yii::t('testing', 'not_change_state')
            );
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     *
     * @return bool
     */
    protected function actionRunBlockTests()
    {
        $blockModule = $this->service->getStructure();

        $script = \Yii::$app->view->renderPhpFile(
            __DIR__ . '/templates/testBlock.php',
            [
                'pathToSite' => ROOTPATH,
                'blockTest' => Helper::getShortPathById($blockModule->getId()),
                'title' => $blockModule->getTitle(),
            ]
        );

        $nameScript = 'testBlock.sh';
        if (Helper::createFileForRun($nameScript, $script)) {
            $this->addMessage('Файл успешно создан', 'Его запуск происходит в ручном режиме');
        } else {
            throw new \Exception(\Yii::t('testing', 'not_create_executable_file'));
        }

        return true;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     *
     * @return bool
     */
    protected function actionRunModuleTests()
    {
        $data = $this->get('data');
        $blockTest = ArrayHelper::getValue($data, 'id');
        $title = ArrayHelper::getValue($data, 'title');
        $script = \Yii::$app->view->renderPhpFile(
            __DIR__ . '/templates/testBlock.php',
            [
                'pathToSite' => ROOTPATH,
                'blockTest' => $blockTest,
                'title' => $title,
            ]
        );

        $nameScript = 'testBlock.sh';
        if (Helper::createFileForRun($nameScript, $script)) {
            $this->addMessage('Файл успешно создан', 'Его запуск происходит в ручном режиме');
        } else {
            throw new \Exception(\Yii::t('testing', 'not_create_executable_file'));
        }

        return true;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \skewer\base\ft\Exception
     */
    protected function actionRunTestSuite()
    {
        $path = $this->getStr('path');

        if ($path) {
            $output = $this->service->runTestSuite($path);

            if (empty($output)) {
                throw new \Exception(\Yii::t('testing', 'not_rights_run_bash'));
            }

            $this->addMessage('Тест выполнился', 'Перейдите в детальную файла');

            return true;
        }

        throw new \Exception('Не достаточно информации. Передайте путь к Test Suite');
    }
}
