<?php

namespace unit\base\site_module;

use skewer\base\site\Layer;
use skewer\base\site_module\Module;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.03.14
 * Time: 10:03.
 */
class ModuleTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\base\site_module\Module::getClassOrExcept
     * @dataProvider providerGetClassOrExcept
     *
     * @param $sClassName
     * @param $sName
     * @param $sLayer
     * @param string $sType
     */
    public function testGetClassOrExcept($sClassName, $sName, $sLayer, $sType = null)
    {
        if (!$sClassName) {
            $this->expectException('\yii\web\ServerErrorHttpException');
        }
        if (func_num_args() == 3) {
            $this->assertSame($sClassName, Module::getClassOrExcept($sName, $sLayer));
        } else {
            $this->assertSame($sClassName, Module::getClassOrExcept($sName, $sLayer, $sType));
        }
    }

    public function providerGetClassOrExcept()
    {
        return [
            /* namespace + */
            ['skewer\build\Page\CatalogViewer\Module', 'CatalogViewer', ''],
            ['skewer\build\Page\CatalogViewer\Module', 'CatalogViewer', Layer::PAGE],
            ['skewer\build\Adm\Catalog\Module', 'Catalog', Layer::ADM],

            ['skewer\build\Adm\Catalog\Install', 'Catalog', Layer::ADM, 'Install'],

            ['skewer\build\Adm\Catalog\Module', 'Adm\Catalog', Layer::ADM],
            ['skewer\build\Page\CatalogViewer\Module', 'Page\CatalogViewer', Layer::PAGE],

            ['skewer\build\Adm\Params\Module', 'skewer\build\Adm\Params\Module', Layer::ADM],

            ['', 'Page\Catalog', Layer::ADM],
        ];
    }

    /**
     * @covers \skewer\base\site_module\Module::getClass
     * @dataProvider providerGetClass
     *
     * @param $sClassName
     * @param $sName
     * @param $sLayer
     * @param string $sType
     */
    public function testGetClass($sClassName, $sName, $sLayer, $sType = null)
    {
        if (func_num_args() == 3) {
            $this->assertSame($sClassName, Module::getClass($sName, $sLayer));
        } else {
            $this->assertSame($sClassName, Module::getClass($sName, $sLayer, $sType));
        }
    }

    public function providerGetClass()
    {
        return [
            /* namespace + */
            ['skewer\build\Page\CatalogViewer\Module', 'CatalogViewer', ''],
            ['skewer\build\Page\CatalogViewer\Module', 'CatalogViewer', Layer::PAGE],
            ['skewer\build\Adm\Catalog\Module', 'Catalog', Layer::ADM],

            ['skewer\build\Adm\Catalog\Install', 'Catalog', Layer::ADM, 'Install'],

            ['skewer\build\Adm\Catalog\Module', 'Adm\Catalog', Layer::ADM],
            ['skewer\build\Page\CatalogViewer\Module', 'Page\CatalogViewer', Layer::PAGE],

            ['skewer\build\Adm\Params\Module', 'skewer\build\Adm\Params\Module', Layer::ADM],

            ['skewer\build\Page\Catalog\Module', 'Page\Catalog', Layer::ADM],
        ];
    }
}
