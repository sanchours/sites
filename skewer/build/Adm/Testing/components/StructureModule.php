<?php

namespace skewer\build\Adm\Testing\components;

/**
 * Class StructureModule - Статистика по модулю.
 */
class StructureModule extends Structure
{
    private $percent = 0;
    private $passedAutotest = 0;
    private $checkManualTest = 0;

    /**
     * StructureModule constructor.
     *
     * @param $title
     * @param $path
     * @param string $parent
     *
     * @throws \skewer\base\ft\Exception
     */
    public function __construct($title, $path, $parent = '')
    {
        parent::__construct($title, $path, $parent);
    }

    /**
     * @throws \skewer\base\ft\Exception
     *
     * @return null|array
     */
    public function getArrayFieldTest()
    {
        $this->setPrivateParam();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'path' => $this->path,
            'percent' => $this->percent,
            'passedAutotest' => $this->passedAutotest,
            'checkManualTest' => $this->checkManualTest,
        ];
    }

    /**
     * @throws \skewer\base\ft\Exception
     */
    private function setPrivateParam()
    {
        $testSuites = Helper::getTestSuitesModule($this->path);
        $count = count($testSuites);

        if ($count !== 0) {
            $autotestCount = 0;
            $autotestPassedCount = 0;

            $manualCount = 0;
            $manualCheckCount = 0;

            foreach ($testSuites as $testSuite) {
                if ($testSuite->autotest) {
                    ++$autotestCount;
                    $codeResultLastRun = $testSuite->getResultLastRun();
                    if ($codeResultLastRun == StructureTestSuite::PASSED) {
                        ++$autotestPassedCount;
                    }
                } else {
                    ++$manualCount;
                    $checkManual = ManualTestEntity::getByPathId($testSuite->id);
                    if ($checkManual instanceof ManualTestEntity && $checkManual->check) {
                        ++$manualCheckCount;
                    }
                }
            }

            $this->percent = $count
                ? round($autotestCount / $count, 2) * 100
                : 0;
            $this->passedAutotest = $autotestCount
                ? round($autotestPassedCount / $autotestCount, 2) * 100
                : 0;
            $this->checkManualTest = $manualCount
                ? round($manualCheckCount / $manualCount, 2) * 100
                : 0;
        }
    }
}
