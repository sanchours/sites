<?php

namespace unit\build\libs\Compress;

use Codeception\Test\Unit;
use skewer\libs\Compress\ChangeAssets;

class ChangeAssetsTest extends Unit
{
    public function providerConstruct(): array
    {
        return [
            ['url(test.png)', 'url(/assets/test/test.png)'],
            ['url(../css/test.png)', 'url(/assets/test/css/test.png)'],
            ['url (../css/test.png)', 'url (../css/test.png)'],
            ['blur(25px)', 'blur(25px)'],
            ['test(test.png)', 'test(test.png)'],
        ];
    }

    /**
     * @dataProvider providerConstruct
     *
     * @param array $inputParams
     * @param array $outputParams
     */
    public function testChangePath($inputParams, $outputParams)
    {
        $content = ChangeAssets::changePath($inputParams,
            '/test/assets/test/test.css');
        $this->assertEquals($content, $outputParams);
    }
}
