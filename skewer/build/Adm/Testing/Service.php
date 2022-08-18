<?php

namespace skewer\build\Adm\Testing;

use skewer\build\Adm\Testing\components\Helper;

class Service
{
    private $path;
    private $moduleName;

    const MODULE_TEST_SUITE = 'testSuite';
    const MODULE_BLOCK_TESTS = 'blockTests';

    public function __construct($path)
    {
        $this->path = $path;

        $this->moduleName = Helper::isTestSuiteFormat($this->path)
            ? self::MODULE_TEST_SUITE
            : self::MODULE_BLOCK_TESTS;
    }

    public function getModuleForTabInit()
    {
        $nameModule = 'Module' . ucfirst($this->moduleName);

        return [
            $this->moduleName => "skewer\\build\\Adm\\Testing\\{$nameModule}",
        ];
    }

    public function getParamsModule()
    {
        return [
            $this->moduleName => [
                'cmd' => 'init',
                'pathObject' => $this->path,
            ],
        ];
    }
}
