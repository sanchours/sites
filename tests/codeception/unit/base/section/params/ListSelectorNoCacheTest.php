<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 05.09.2016
 * Time: 13:26.
 */

namespace unit\base\section\params;

use skewer\base\section\ParamCache;

include 'ListSelectorTest.php';

class ListSelectorNoCacheTest extends ListSelectorTest
{
    protected function setUp()
    {
        ParamCache::$useCache = false;
        parent::setUp();
    }

    protected function tearDown()
    {
        ParamCache::$useCache = true;
        parent::tearDown();
    }
}
