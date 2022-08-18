<?php

namespace skewer\build\Adm\Testing;

use skewer\build\Adm\Testing\components\Helper;
use skewer\build\Adm\Testing\components\Structure;
use skewer\build\Adm\Testing\components\StructureTestSuite;

abstract class AbstractService
{
    protected $pathToObject;

    protected $nameObject;

    /** @var Structure */
    protected $structure;

    public function __construct($pathToObject)
    {
        $this->pathToObject = Helper::getPathTestSuiteObject($pathToObject);
        $directories = explode('/', $pathToObject);
        $this->nameObject = $directories[count($directories) - 1];

        $this->setStructure();
    }

    abstract protected function setStructure();

    final public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @param $pathTestSuite
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \skewer\base\ft\Exception
     *
     * @return mixed
     */
    final public function runTestSuite($pathTestSuite)
    {
        $testSuite = $this->getStructureTestSuite($pathTestSuite);

        if ($testSuite) {
            $templateScript = \Yii::$app->view->renderPhpFile(
                __DIR__ . '/templates/runTestSuite.php',
                [
                    'pathToSite' => ROOTPATH,
                    'pathTestSuite' => $testSuite->getShortPath(),
                    'title' => $testSuite->getTitle(),
                ]
            );

            $nameScript = 'runTestSuite.sh';
            if (Helper::createFileForRun($nameScript, $templateScript)) {
                $output = Helper::runAutotest($nameScript);
            } else {
                throw new \Exception(\Yii::t('testing', 'not_create_executable_file'));
            }

            return $output;
        }
        throw new \Exception(\Yii::t('testing', 'not_path_test_suit'));
    }

    /**
     * @param $pathToObject
     *
     * @throws \skewer\base\ft\Exception
     *
     * @return StructureTestSuite
     */
    private function getStructureTestSuite($pathToObject)
    {
        $directories = explode('/', $pathToObject);
        $nameObject = $directories[count($directories) - 1];

        return new StructureTestSuite($nameObject, $pathToObject);
    }
}
