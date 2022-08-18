<?php

namespace skewer\build\Adm\Testing;

use skewer\base\ft\Exception;
use skewer\base\site_module\Parser;
use skewer\build\Adm\Testing\components\Helper;
use skewer\build\Adm\Testing\components\StructureTestSuite;

class ServiceTestSuite extends AbstractService
{
    private $dirTestSuite = '';

    private $dirLastRunTestSuite = '';

    /** @var StructureTestSuite */
    protected $structure;

    /**
     * @throws Exception
     */
    public function setStructure()
    {
        $this->structure = new StructureTestSuite($this->nameObject, $this->pathToObject);
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function getTestCases()
    {
        $pathTestCases = $this->getListTestCases();
        $testCases = [];
        foreach ($pathTestCases as $pathTestCase) {
            $pathScript = $this->getPathScript($pathTestCase);
            $testCases[] = $this->parseFile($pathScript);
        }

        return $testCases;
    }

    public function getShortPathTestSuite()
    {
        return $this->structure->getShortPath();
    }

    public function getReportDirectories($dirLastRun = '')
    {
        $pathTestSuite = $this->structure->getShortPath();

        if (!$this->dirTestSuite) {
            $this->setDirTestSuite($pathTestSuite);
        }
        $dir = $this->dirTestSuite;

        if ($dirLastRun) {
            $this->setLastRunTestSuite($pathTestSuite, $dirLastRun);
            $dir = $this->dirLastRunTestSuite;
        }

        return is_dir($dir)
            ? array_diff(scandir($dir, true), ['.', '..'])
            : null;
    }

    public function getDescriptionTestSuit()
    {
        return Helper::getDescriptionTestSuit($this->pathToObject);
    }

    public function getUrlLastReportData($directories)
    {
        $folderLastRun = array_shift($directories);

        $dir4Save = WEBPATH . "tests/lastReport/{$folderLastRun}";

        if (!is_dir($dir4Save)) {
            $filesLastRun = $this->getReportDirectories($folderLastRun);

            foreach ($filesLastRun as $nameFileLastDir) {
                $sFullDstFileName = "{$dir4Save}/{$nameFileLastDir}";
                if (!is_dir(dirname($sFullDstFileName))) {
                    mkdir(dirname($sFullDstFileName));
                }
                copy(
                    "{$this->dirLastRunTestSuite}/{$nameFileLastDir}",
                    $sFullDstFileName
                );
            }
        }

        return "@web/tests/lastReport/{$folderLastRun}/{$folderLastRun}.html";
    }

    /**
     * Получение списка Test Cases из конкретного Test Suite.
     *
     * @return array
     */
    private function getListTestCases()
    {
        $oStructures = Helper::getStructXML($this->pathToObject, 'testCaseLink');
        $aListTestCase = [];
        /** @var \DOMElement $oStructures */
        foreach ($oStructures as $oStruct) {
            $oSimpleXml = simplexml_import_dom($oStruct);
            if (isset($oSimpleXml->testCaseId) && $oSimpleXml->testCaseId) {
                $aListTestCase[] = $oSimpleXml->testCaseId;
            }
        }

        return $aListTestCase;
    }

    /**
     * Разбор файла с описанием последовательности действий теста.
     *
     * @param $pathToScript
     *
     * @throws Exception
     *
     * @return array
     */
    private function parseFile($pathToScript)
    {
        $pathToScript = Helper::getPathAcceptanceKS() . "/{$pathToScript}";
        if (is_dir($pathToScript)) {
            $aFiles = scandir($pathToScript);
            $sNameFile = '';
            foreach ($aFiles as $sFile) {
                if (mb_stristr($sFile, '.groovy')) {
                    $sNameFile = $sFile;
                    break;
                }
            }
            if (!$sNameFile) {
                throw new Exception(\Yii::t('testing', 'script_not_exist'));
            }

            $sContent = file_get_contents($pathToScript . "/{$sNameFile}");
            preg_match_all('/@description (.*)$/m', $sContent, $aDescription);
            preg_match_all('/@step (.*)$/m', $sContent, $aSteps);

            if ($aDescription[1] || $aSteps[1]) {
                $aResult['description'] = $aDescription[1] ? $this->parseTemplate($aDescription[1]) : '';
                $aResult['steps'] = $aSteps[1] ? $this->parseTemplate($aSteps[1]) : '';

                return $aResult;
            }

            return [];
        }
        throw new Exception(\Yii::t('testing', 'dir_not_exist'));
    }

    /**
     * Путь до файла с описанием тест кейса.
     *
     * @param $pathTestCase
     *
     * @return mixed
     */
    private function getPathScript($pathTestCase)
    {
        return str_replace('Test Cases', 'Scripts', $pathTestCase);
    }

    private function parseTemplate($data)
    {
        return Parser::parseTwig('list.twig', ['aData' => $data], __DIR__ . '/templates');
    }

    private function setDirTestSuite($pathTestSuite)
    {
        $dirTestSuite = mb_stristr($pathTestSuite, '/');
        $this->dirTestSuite = Helper::getPathAcceptanceKS() . "/Reports{$dirTestSuite}";
    }

    private function setLastRunTestSuite($pathTestSuite, $dirLastRun)
    {
        if (!$this->dirTestSuite) {
            $this->setDirTestSuite($pathTestSuite);
        }

        $this->dirLastRunTestSuite = $this->dirTestSuite . "/{$dirLastRun}";
    }

    /**
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function runCurrentTestSuite()
    {
        return $this->runTestSuite($this->pathToObject);
    }
}
