<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 21.12.2017
 * Time: 9:52.
 */
use skewer\base\site\Site;
use skewer\build\Tool\Domains;

class SiteTest extends \Codeception\Test\Unit
{
    protected $sOldDomain;

    protected function setUp()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->sOldDomain = $_SERVER['HTTP_HOST'];
        } else {
            $this->sOldDomain = null;
        }

        Domains\Api::clearCache();

        $_SERVER['HTTP_HOST'] = 'test.ru';
    }

    protected function tearDown()
    {
        Domains\Api::clearCache();

        if ($this->sOldDomain === null) {
            unset($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = $this->sOldDomain;
        }
    }

    public function providerAdmUrl()
    {
        return [
            ['http://test.ru/admin/#out.left.tools=Forms;out.tabs=tools_Forms',
                'Forms', null, null, ],
            ['http://test.ru/admin/#out.left.tools=Forms;out.tabs=tools_Forms',
                'Forms', 'tools', '', ],
            ['http://test.ru/admin/#out.left.tools=Forms;out.tabs=tools_Forms;init_tab=tools_Forms;init_param=43',
                'Forms', 'tools', 43, ],
            ['http://test.ru/admin/#out.left.tools=Orders;out.tabs=tools_Orders',
                'Orders', 'tools', '', ],
            ['http://test.ru/admin/#out.left.tools=Orders;out.tabs=tools_Orders;init_tab=tools_Orders;init_param=543',
                'Orders', 'tools', 543, ],
            ['http://test.ru/admin/#out.left.catalog=Goods;out.tabs=catalog_Goods',
                'Goods', 'catalog', '', ],
            ['http://test.ru/admin/#out.left.catalog=Goods;out.tabs=catalog_Goods;init_tab=catalog_Goods;init_param=8',
                'Goods', 'catalog', 8, ],
        ];
    }

    /**
     * @covers \skewer\base\site\Site::admUrl
     * @dataProvider providerAdmUrl
     *
     * @param mixed $url
     * @param mixed $sNameModule
     * @param mixed $sLayout
     * @param mixed $sParam
     */
    public function testAdmUrl($url, $sNameModule, $sLayout, $sParam)
    {
        if ($sLayout === null) {
            $this->assertSame($url, Site::admUrl($sNameModule));
        } else {
            $this->assertSame($url, Site::admUrl($sNameModule, $sLayout, $sParam));
        }
    }

    public function providerTreeUrl()
    {
        return [
            ['http://test.ru/admin/#out.left.tree=123;out.tabs=tree_Forms',
                123, 'Forms', 'tree', '', ],
            ['http://test.ru/admin/#out.left.lib=48;out.tabs=lib_editor',
                48, 'editor', 'lib', '', ],
            ['http://test.ru/admin/#out.left.lib=48;out.tabs=lib_editor;init_tab=lib_editor;init_param=12_312',
                48, 'editor', 'lib', '12_312', ],
            ['http://test.ru/admin/#out.left.section=123;out.tabs=section_Forms',
                123, 'Forms', null, '', ],
            ['http://test.ru/admin/#out.left.section=123',
                123, null, null, '', ],
            ['http://test.ru/admin/#out.left.section=456;out.tabs=section_obj_content__Catalog;init_tab=section_obj_content__Catalog;init_param=789',
                456, 'Catalog', 'section', 789, 'content', ],
        ];
    }

    /**
     * @covers       \skewer\base\site\Site::admTreeUrl
     * @dataProvider providerTreeUrl
     *
     * @param $url
     * @param $section
     * @param $module
     * @param $layout
     * @param $param
     * @param null|mixed $label
     */
    public function testAdmTreeUrl($url, $section, $module, $layout, $param, $label = null)
    {
        if ($section === null) {
            $this->assertSame($url, Site::admTreeUrl($section));
        }
        if ($layout === null) {
            $this->assertSame($url, Site::admTreeUrl($section, $module));
        } else {
            $this->assertSame($url, Site::admTreeUrl($section, $module, $layout, $param, $label));
        }
    }
}
