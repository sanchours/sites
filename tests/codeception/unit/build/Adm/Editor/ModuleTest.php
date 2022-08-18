<?php
/**
 * Created by PhpStorm.
 * User: ilya
 * Date: 19.03.14
 * Time: 12:45.
 */

namespace unit\build\Adm\Editor;

use skewer\build\Adm\Editor\Module;
use skewer\build\Tool\Domains;

/**
 *  codecept run codeception/unit/build/Adm/Editor/ModuleTest.php
 * Class ModuleText.
 */
class ModuleTest extends \Codeception\Test\Unit
{
    /**
     * @var null|string
     */
    private $sOldDomain = '';

    protected function setUp()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->sOldDomain = $_SERVER['HTTP_HOST'];
        } else {
            $this->sOldDomain = null;
        }

        Domains\Api::clearCache();

        $_SERVER['HTTP_HOST'] = 'my-test-domain.twinslab.ru';
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

    public function providerConvertBadLinks()
    {
        $sHost = 'my-test-domain.twinslab.ru';

        return [
            ['<a href="http://' . $sHost . '/my-test">link_1</a>', '<a href="/my-test">link_1</a>'],
            ['<a href="https://' . $sHost . '/my-test">link_2</a>', '<a href="/my-test">link_2</a>'],
            ['<a href="http://google.com/my-test">link_3</a>', '<a rel="nofollow" href="http://google.com/my-test">link_3</a>'],
            ['<a href="/my-test">link_4</a>', '<a href="/my-test">link_4</a>'],
            ['<a rel="nofollow" href="http://google.com/my-test">link_3</a>', '<a rel="nofollow" href="http://google.com/my-test">link_3</a>'],
            ['<a rel="nofollow" rel="nofollow" href="http://google.com/my-test">link_3</a>', '<a rel="nofollow" href="http://google.com/my-test">link_3</a>'],
        ];
    }

    /**
     * @covers \skewer\build\Adm\Editor\Module::convertBadLinks
     * @dataProvider providerConvertBadLinks
     *
     * @param mixed $sInText
     * @param mixed $sOutText
     */
    public function testConvertBadLinks($sInText, $sOutText)
    {
        $this->assertEquals(
            $sOutText,
            Module::convertBadLinks($sInText),
            'ERROR! Link converting fail'
        );

        /*2 прогона теста*/
        $this->assertEquals(
            $sOutText,
            Module::convertBadLinks(Module::convertBadLinks($sInText)),
            'ERROR! Link converting fail in 2 calls'
        );

        /*3 прогона теста*/
        $this->assertEquals(
            $sOutText,
            Module::convertBadLinks(Module::convertBadLinks(Module::convertBadLinks($sInText))),
            'ERROR! Link converting fail in 3 calls'
        );
    }
}
