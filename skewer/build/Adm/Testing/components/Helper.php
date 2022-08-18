<?php

namespace skewer\build\Adm\Testing\components;

use skewer\base\ft\Exception;
use skewer\components\import\provider\XmlReader;

class Helper
{
    const ACCEPT_FORMAT_FILE = '.ts';
    const SIGN_REPLACE_FOR_ID = '\\';

    public static function getPathAcceptanceKS()
    {
        return ROOTPATH . 'tests/acceptanceKS';
    }

    public static function getPathTestSuite()
    {
        return ROOTPATH . 'tests/acceptanceKS/Test Suites';
    }

    public static function getPathTestSuiteObject($shortPath)
    {
        return self::getPathTestSuite() . $shortPath;
    }

    /**
     * Получение структуры файлов для документации.
     *
     * @param $sPathDir
     *
     * @throws Exception
     *
     * @return array
     */
    public static function getStructTestCases($sPathDir)
    {
        return self::getTesting($sPathDir, true);
    }

    /**
     * @param $sPathChildren
     *
     * @throws Exception
     *
     * @return array
     */
    public static function getTreeByChildrenId($sPathChildren)
    {
        $separator = '/';
        $path = '';
        $aChildren = explode($separator, $sPathChildren);
        $allTree = [];
        foreach ($aChildren as $value) {
            if ($value) {
                $path = ($path === $separator)
                    ? $path . $value
                    : $path . $separator . $value;
                $parentDir = self::getStructTestCases(
                    self::getPathTestSuiteObject($path)
                );
                $allTree = array_merge($allTree, $parentDir);
            }
        }

        return $allTree;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public static function getRootStructure()
    {
        return self::getTesting(Helper::getPathTestSuite());
    }

    /**
     * @param $sPathDir
     * @param bool $setParent
     *
     * @throws Exception
     *
     * @return array
     */
    private static function getTesting($sPathDir, $setParent = false)
    {
        $aObjects = self::getFileFromDir($sPathDir);

        $aListResult = [];

        if ($aObjects) {
            foreach ($aObjects as $sObject) {
                $sPathFile = $sPathDir . "/{$sObject}";
                $sParent = $setParent ? $sPathDir : '';
                $oStructure = new Structure($sObject, $sPathFile, $sParent);
                if ($oStructure->canAddInList()) {
                    $fieldTest = $oStructure->getArrayFieldTest();
                    if (is_array($fieldTest)) {
                        $aListResult[] = $fieldTest;
                    }
                }
            }
        }

        return $aListResult;
    }

    /**
     * Структура xml документа.
     *
     * @param $sPath
     * @param $sParentMarkup
     *
     * @return \DOMNodeList
     */
    public static function getStructXML($sPath, $sParentMarkup)
    {
        $oDom = new \DOMDocument();
        $oDom->load($sPath);
        /** @var \DOMNodeList $oOffers */
        $oStructures = XmlReader::queryXPath(new \DOMXPath($oDom), '//' . $sParentMarkup);

        return $oStructures;
    }

    /**
     * Получение описания Test Suite из файла.
     *
     * @param $sPath
     *
     * @return string
     */
    public static function getDescriptionTestSuit($sPath)
    {
        $oStructures = self::getStructXML($sPath, 'TestSuiteEntity');
        if ($oStructures) {
            /** @var \DOMElement $oStructures */
            foreach ($oStructures as $oStruct) {
                $oSimpleXml = simplexml_import_dom($oStruct);

                return (isset($oSimpleXml->description)) ? trim($oSimpleXml->description) : '';
            }
        }

        return '';
    }

    public static function createFileForRun($nameFile, $templateScript)
    {
        if (!is_dir(AUTOTEST_BASH_PATH)) {
            mkdir(AUTOTEST_BASH_PATH);
        }

        $pathRunAutotest = AUTOTEST_BASH_PATH . $nameFile;
        file_put_contents($pathRunAutotest, $templateScript);
        chmod($pathRunAutotest, 0755);

        return is_file($pathRunAutotest);
    }

    public static function runAutotest($nameBashFile)
    {
        exec(AUTOTEST_BASH_PATH . "/{$nameBashFile}", $output, $return_var);

        return $output;
    }

    public static function getFileFromDir($pathDir)
    {
        return array_diff(scandir($pathDir), ['.', '..', '.meta']);
    }

    /**
     * @param $pathBlockTests
     *
     * @throws Exception
     *
     * @return StructureTestSuite[]
     */
    public static function getTestSuitesModule($pathBlockTests)
    {
        $folders = Helper::getFileFromDir($pathBlockTests);

        $testSuites = [];

        if ($folders) {
            foreach ($folders as $folder) {
                if (mb_strpos($folder, Helper::ACCEPT_FORMAT_FILE)) {
                    $pathTestSuite = $pathBlockTests . "/{$folder}";
                    $testSuite = new StructureTestSuite($folder, $pathTestSuite);
                    if ($testSuite->canAddInList()) {
                        $testSuites[] = $testSuite;
                    }
                }
            }
        }

        return $testSuites;
    }

    /**
     * @param $pathBlockTests
     *
     * @throws Exception
     *
     * @return array
     */
    public static function getTestSuitesWithResult($pathBlockTests)
    {
        $folders = Helper::getFileFromDir($pathBlockTests);

        $testSuites = [];

        if ($folders) {
            foreach ($folders as $folder) {
                if (mb_strpos($folder, Helper::ACCEPT_FORMAT_FILE)) {
                    $pathTestSuite = $pathBlockTests . "/{$folder}";
                    $testSuite = new StructureTestSuite($folder, $pathTestSuite);
                    if ($testSuite->canAddInList()) {
                        $testSuites[] = $testSuite->getTestSuitesWithResultRun();
                    }
                }
            }
        }

        return $testSuites;
    }

    public static function isTestSuiteFormat($path)
    {
        return mb_strpos($path, Helper::ACCEPT_FORMAT_FILE);
    }

    public static function getIdByShortPath($shortPath)
    {
        return str_replace('/', self::SIGN_REPLACE_FOR_ID, $shortPath);
    }

    public static function getShortPathById($idStructure)
    {
        return str_replace(self::SIGN_REPLACE_FOR_ID, '/', $idStructure);
    }
}
