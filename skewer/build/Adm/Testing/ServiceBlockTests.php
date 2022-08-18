<?php

namespace skewer\build\Adm\Testing;

use skewer\base\ft\Exception;
use skewer\base\site\Site;
use skewer\build\Adm\Testing\components\Helper;
use skewer\build\Adm\Testing\components\ManualTestEntity;
use skewer\build\Adm\Testing\components\Structure;
use skewer\build\Adm\Testing\components\StructureModule;

class ServiceBlockTests extends AbstractService
{
    /**
     * @throws Exception
     */
    protected function setStructure()
    {
        $this->structure = new Structure($this->nameObject, $this->pathToObject);
    }

    /**
     * @return null|mixed|string
     */
    public function getTitleBlockTests()
    {
        return $this->structure->getTitle();
    }

    /**
     * Проверка на наличие Test Suites - проверка первого файла папки.
     *
     * @return bool
     */
    public function hasTestSuites()
    {
        if (is_dir($this->pathToObject)) {
            $objects = Helper::getFileFromDir($this->pathToObject);
        } else {
            return false;
        }

        return is_file("{$this->pathToObject}/" . current($objects));
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function getTestSuitesModule()
    {
        $testSuites = Helper::getTestSuitesWithResult($this->pathToObject);
        foreach ($testSuites as &$testSuite) {
            if (isset($testSuite['autotest']) && $testSuite['autotest']) {
                $link = Site::httpDomainSlash() . "admin/#out.left.testing={$testSuite['id']};out.tabs=testSuite";
                $testSuite['link'] = "<a href='{$link}'>{$testSuite['title']}</a>";
            } else {
                $testSuite['link'] = $testSuite['title'];
            }
        }

        return $testSuites;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function getTestsModules()
    {
        $folders = Helper::getFileFromDir($this->pathToObject);

        $modules = [];

        if ($folders) {
            foreach ($folders as $folder) {
                $pathModule = $this->pathToObject . "/{$folder}";
                $module = new StructureModule($folder, $pathModule);
                if ($module->canAddInList()) {
                    $blockTest = $module->getArrayFieldTest();

                    $link = Site::httpDomainSlash() . "admin/#out.left.testing={$blockTest['id']};out.tabs=blockTests";
                    $blockTest['link'] = "<a href='{$link}'>{$blockTest['title']}</a>";

                    $modules[] = $blockTest;
                }
            }
        }

        return $modules;
    }

    public function changeState($pathId)
    {
        $manualTest = ManualTestEntity::getByPathId($pathId);
        if ($manualTest instanceof ManualTestEntity) {
            $manualTest->check = $manualTest->check ? false : true;
        } else {
            $manualTest = new ManualTestEntity();
            $manualTest->path_id = $pathId;
            $manualTest->check = true;
        }

        return $manualTest->save();
    }
}
