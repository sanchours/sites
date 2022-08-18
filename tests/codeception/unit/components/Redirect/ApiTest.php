<?php

namespace unit\components\Redirect;

use skewer\base\SysVar;
use skewer\build\Tool\Domains;
use skewer\components\redirect\Api;

/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.07.2016
 * Time: 11:12
 * To run this test use: codecept run codeception/unit/components/Redirect/ApiTest.php.
 */
class ApiTest extends \Codeception\Test\Unit
{
    /**
     * @var \skewer\components\redirect\Api
     */
    protected $object;

    public function providerRedirect()
    {
        return [
            ['/qwe/(.+)', '/asd/$1', '/qwe/asdfas/', '/asd/asdfas/'],
            ['/qwe/(.+)', '/asd/$1', 'http://example.ru/qwe/asdfas/', 'http://example.ru/asd/asdfas/'],

            ['/akcii-i-specpredlozhenija/', '/contacts/', '/akcii-i-specpredlozhenija/', '/contacts/'],

            ['^/akcii-i-specpredlozhenija/(.*)$', '/contacts/', '/akcii-i-specpredlozhenija/news1/', '/contacts/'],
            ['^/akcii-i-specpredlozhenija/(.*)$', '/contacts/', '/akcii-i-specpredlozhenija/', '/contacts/'],

            ['^/akcii-i-specpredlozhenija/(.+)$', '/contacts/', '/akcii-i-specpredlozhenija/news1/', '/contacts/'],
            ['^/akcii-i-specpredlozhenija/(.+)$', '/contacts/', '/akcii-i-specpredlozhenija/', null],

            ['(.*)/new1/', '/newurl/', '/news/new1/', '/newurl/'],
            ['(.*)/new1/$', '/newurl/', '/news/new1/', '/newurl/'],
            ['^(.*)/new1/$', '/newurl/', '/news/new1/', '/newurl/'],
        ];
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new \skewer\components\redirect\Api();
        Domains\Api::clearCache();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        Domains\Api::clearCache();
    }

    /**
     * @covers \skewer\components\redirect\Api::setTestUrls
     */
    public function testSetTestUrls()
    {
        /*Достанем старый текст и запишем новый*/
        $sOldText = \unit\data\TestHelper::callPrivateMethod($this->object, 'getTestUrls');
        \unit\data\TestHelper::callPrivateMethod($this->object, 'setTestUrls', [['test_content']]);

        /*Достанем новый текст и вернем старый*/
        $sNewText = \unit\data\TestHelper::callPrivateMethod($this->object, 'getTestUrls');
        \unit\data\TestHelper::callPrivateMethod($this->object, 'setTestUrls', [[$sOldText]]);

        $this->assertEquals('test_content', $sNewText, 'Can not write urls to SysVars');
    }

    /**
     * @covers \skewer\components\redirect\Api::getTestUrls
     */
    public function testGetTestUrls()
    {
        $sText = SysVar::get('testUrls');
        $sMethodText = \unit\data\TestHelper::callPrivateMethod($this->object, 'getTestUrls');

        $this->assertEquals(
            $sText,
            $sMethodText,
            'Can not get test urls from SysVar'
        );
    }

    /**
     * @covers \skewer\components\redirect\Api::tryRegExpRedirect
     */
    public function testTryRegExpRedirect()
    {
        $_SERVER['HTTP_HOST'] = 'test.ru';

        \Yii::$app->db
            ->createCommand('DELETE FROM redirect301')
            ->execute();

        $sOldUrl = '/oldurl/';
        $sNewUrl = '/newurl/';

        \Yii::$app->db
            ->createCommand("INSERT INTO redirect301(id,old_url,new_url,priority) VALUES ('1','" . $sOldUrl . "','" . $sNewUrl . "','1')")
            ->execute();

        \Yii::$app->db
            ->createCommand("INSERT INTO redirect301(id,old_url,new_url,priority) VALUES ('2','secondary','secondary_new','2')")
            ->execute();

        \Yii::$app->db
            ->createCommand("INSERT INTO `redirect301` (`id`, `old_url`, `new_url`, `priority`) VALUES
                            (3, '^/myurl(.*)$', '/mynewurl', 3),
                            (4, '^/secondarymyurl/(.+)$', '/newsecondarymyurl', 4);")
            ->execute();

        /***********************************************************/
        /*URL не задан, ID редиректа не задан*/
        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [null, 0]);
        $this->assertEquals('', $sRes, 'ERROR. TryRegExpRedirect without url and redirect_id');

        /**********************************************************/
        /*URL не задан, ID редиректа задан*/
        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [null, 1]);
        $this->assertEquals('', $sRes, 'ERROR. TryRegExpRedirect without url');

        /***********************************************************/
        /*URL задан, ID редиректа не задан*/
        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [$_SERVER['HTTP_HOST'] . '/some-url', 0]);
        $this->assertEquals('/some-url', '/' . $sRes, 'ERROR. TryRegExpRedirect without redirect_id');

        /***********************************************************/
        /*URL задан, ID редиректа задан*/
        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [$_SERVER['HTTP_HOST'] . $sOldUrl, 2]);
        $this->assertEquals($sOldUrl, '/' . $sRes, 'ERROR. TryRegExpRedirect with url and redirect_id');

        /*Тестирование регулярок.*/
        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [$_SERVER['HTTP_HOST'] . '//myurl', 0]);
        $this->assertEquals('/mynewurl', $sRes, 'ERROR. TryRegExpRedirect with RegExp');

        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [$_SERVER['HTTP_HOST'] . '//myurl/666', 0]);
        $this->assertEquals('/mynewurl', $sRes, 'ERROR. TryRegExpRedirect with RegExp');

        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [$_SERVER['HTTP_HOST'] . '//secondarymyurl', 0]);
        $this->assertEquals('/secondarymyurl', $sRes, 'ERROR. TryRegExpRedirect with RegExp');

        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryRegExpRedirect', [$_SERVER['HTTP_HOST'] . '//secondarymyurl/666', 0]);
        $this->assertEquals('/newsecondarymyurl', $sRes, 'ERROR. TryRegExpRedirect with RegExp');
    }

    /**
     * @covers \skewer\components\redirect\Api::tryDomainRedirect
     */
    public function testTryDomainRedirect()
    {
        \Yii::$app->db
            ->createCommand('UPDATE domains SET prim=0')
            ->execute();

        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryDomainRedirect');

        /*Вообще метод должен вернуть $_SERVER['HTTP_HOST']/, но т.к. запускается из консоли будет просто / */
        $this->assertEquals('/', $sRes, 'Error when primary domain does not setted');

        $sTestDomain = 'domain.ru';
        $sTestUri = 'some-uri';

        \Yii::$app->db
            ->createCommand("INSERT INTO domains(`domain_id`,`domain`,`prim`)  VALUES('1','" . $sTestDomain . "','1')")
            ->execute();

        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'tryDomainRedirect', [$sTestUri]);

        $this->assertEquals($sTestDomain . '/' . $sTestUri, $sRes, 'Error when primary domain setted and use adm mode');
    }

    /**
     * @covers \skewer\components\redirect\Api::createValidUrl
     */
    public function testCreateValidUrl()
    {
        $_SERVER['HTTP_HOST'] = 'test.ru';
        $_SERVER['REQUEST_URI'] = '/test';
        $sRes = \unit\data\TestHelper::callPrivateMethod($this->object, 'createValidUrl', ['/myurl']);

        Api::$sNewDomain = null;
        $this->assertEquals('http://domain.ru/myurl', $sRes, 'ERROR. CreateValidUrl error');
    }

    /**
     * @covers \skewer\components\redirect\Api::useRedirect
     */
    public function testUseRedirect()
    {
        $sVal = 1;
        if (\skewer\base\site\Server::isApache()) {
            $sVal = \skewer\base\SysVar::get('useApacheRedirect');
        } elseif (\skewer\base\site\Server::isNginx()) {
            $sVal = \skewer\base\SysVar::get('useNginxRedirect');
        }

        $this->assertEquals($sVal, \unit\data\TestHelper::callPrivateMethod($this->object, 'useRedirect'), 'Invalid redirect mode on server');
    }

    /**
     * @covers \skewer\components\redirect\Api::useRedirect
     * @dataProvider providerRedirect
     *
     * @param mixed $old
     * @param mixed $new
     * @param mixed $in
     * @param mixed $out
     */
    public function testRedirect($old, $new, $in, $out)
    {
        $this->assertEquals(
            $out,
            Api::checkRule($old, $new, $in)
        );
    }
}
