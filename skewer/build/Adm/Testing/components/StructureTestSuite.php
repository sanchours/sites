<?php

namespace skewer\build\Adm\Testing\components;

use skewer\base\ft\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class StructureTestSuite - статистика по TestSuite.
 */
class StructureTestSuite extends Structure
{
    private $manual = false;

    const NOT_PASSED = -1;
    const NOT_RUNNING = 0;
    const PASSED = 1;

    /**
     * StructureTestSuite constructor.
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

        if ($this->autotest === false) {
            $this->title = $this->titleWithoutLabel;
            $this->autotest = false;

            $manualTest = ManualTestEntity::getByPathId($this->id);
            if ($manualTest instanceof ManualTestEntity) {
                $this->manual = $manualTest->check;
            }
        }
    }

    public function getShortPath()
    {
        $shortPath = mb_stristr($this->path, 'Test Suites');

        return mb_stristr($shortPath, Helper::ACCEPT_FORMAT_FILE, true);
    }

    public function getArrayFieldTest()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'path' => $this->path,
            'autotest' => (bool) $this->autotest,
            'manual' => (bool) $this->manual,
        ];
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function getTestSuitesWithResultRun()
    {
        $codeResultRun = $this->getResultLastRun();
        $resultLastRun = $this->getResultLastRunTitle($codeResultRun);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'path' => $this->path,
            'autotest' => (bool) $this->autotest,
            'manual' => (bool) $this->manual,
            'resultLastRun' => $resultLastRun,
        ];
    }

    private function getResultLastRunTitle($codeResult)
    {
        $validValues = [
            self::NOT_PASSED => \Yii::t('testing', 'not_passed'),
            self::NOT_RUNNING => \Yii::t('testing', 'not_running'),
            self::PASSED => \Yii::t('testing', 'passed'),
        ];

        return ArrayHelper::getValue($validValues, $codeResult, '');
    }

    /**
     * Получение информации их файла последнего запуска о результате тестирования.
     *
     * @throws Exception
     *
     * @return int
     */
    public function getResultLastRun()
    {
        if ($this->autotest) {
            $pathTestSuite = $this->getShortPath();
            $pathReport = Helper::getPathAcceptanceKS() . '/' . str_replace('Test Suites', 'Reports', $pathTestSuite);
            if (is_dir($pathReport)) {
                $dirs = Helper::getFileFromDir($pathReport);
                $lastRunFolder = end($dirs);
                $fileResultRun = "{$pathReport}/{$lastRunFolder}/JUnit_Report.xml";
                if (!is_file($fileResultRun)) {
                    throw new Exception('Файл JUnit_Report.xml не создан. Автотест в процессе работы');
                }
                $oStructures = Helper::getStructXML($fileResultRun, 'testsuites');
                /** @var \DOMElement $oStructures */
                foreach ($oStructures as $oStruct) {
                    $oSimpleXml = simplexml_import_dom($oStruct);
                    $attributes = $oSimpleXml->attributes();
                    if (isset($attributes['failures'], $attributes['errors'])) {
                        $failures = (int) $attributes['failures'];
                        $errors = (int) $attributes['errors'];

                        if ($failures !== 0 || $errors !== 0) {
                            return -1;
                        }

                        return 1;
                    }

                    throw new Exception('Структура Test Suite изменилась. Проверьте структуру файлов JUnit_Report.xml');
                }
            }

            return 0;
        }
    }
}
