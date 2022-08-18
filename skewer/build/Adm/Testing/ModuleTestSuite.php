<?php

namespace skewer\build\Adm\Testing;

use skewer\base\site_module\Context;
use skewer\build\Adm\Testing\view\TestResult;
use skewer\build\Adm\Testing\view\TestSuite;
use yii\helpers\Url;

class ModuleTestSuite extends AbstractModule
{
    /** @var ServiceTestSuite $service */
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
        $pathTestSuite = $params['pathObject'] ?? '';

        if (!$pathTestSuite) {
            throw new \Exception(\Yii::t('testing', 'not_test_suite'));
        }

        $this->service = new ServiceTestSuite($pathTestSuite);
    }

    /**
     * @throws \Exception
     */
    protected function actionInit()
    {
        $name = $this->service->getDescriptionTestSuit();

        $testCases = $this->service->getTestCases();

        $dirLastRunTestSuite = $this->service->getReportDirectories();

        if ($testCases) {
            return $this->render(new TestSuite([
                'name' => $name,
                'testCases' => $testCases,
                'isLastRun' => $dirLastRunTestSuite !== null,
            ]));
        }

        throw new \Exception(\Yii::t('testing', 'not_description_test'));
    }

    /**
     * @throws \Exception
     */
    public function actionInitTab()
    {
        return $this->actionInit();
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    protected function actionRunTestSuite()
    {
        $output = $this->service->runCurrentTestSuite();

        if (empty($output)) {
            throw new \Exception(\Yii::t('testing', 'not_rights_run_bash'));
        }

        return $this->getReportData();
    }

    /**
     * @throws \Exception
     */
    public function getReportData()
    {
        $directories = $this->service->getReportDirectories();

        if ($directories) {
            $url = $this->service->getUrlLastReportData($directories);

            return $this->render(new TestResult([
                'settings' => [
                    'src' => Url::to($url, true),
                ],
            ]));
        }

        throw new \Exception(\Yii::t('testing', 'fail_get_last_run'));
    }

    /**
     * @throws \Exception
     */
    public function actionLastRun()
    {
        return $this->getReportData();
    }
}
