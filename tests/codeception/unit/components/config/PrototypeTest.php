<?php

namespace unit\components\config;

use yii\helpers\ArrayHelper;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2013-03-13 at 16:19:45.
 */
class PrototypeTest extends \Codeception\Test\Unit
{
    /**
     * @var ConfigTestClass
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @covers /skewer/components/config/Prototype::__construct
     * @covers /skewer/components/config/Prototype::setData
     */
    protected function setUp()
    {
        require_once 'ConfigTestClass.php';
        $this->config = new ConfigTestClass(require('config/config.php'));
    }

    /**
     * @covers \skewer\components\config\Prototype::__construct
     */
    public function testLoad()
    {
        $this->config = new ConfigTestClass(ArrayHelper::merge(
            require('config/config.php'),
            require('config/configOverlay.php')
        ));
    }

    /**
     * Тест метода установки.
     *
     * @covers \skewer\components\config\Prototype::get
     * @covers \skewer\components\config\Prototype::getByArray
     */
    public function testGet()
    {
        $this->assertSame(
            123,
            $this->config->get('test_val_123'),
            'первичные данные не заданы'
        );

        $this->assertSame(
            1,
            $this->config->get('test_arr.a'),
            'не отдает данные по строке'
        );

        $this->assertSame(
            147,
            $this->config->get(['test_arr', 'b']),
            'не отдает данные по массиву'
        );
    }

    /**
     * Тест метода установки.
     *
     * @covers \skewer\components\config\Prototype::exists
     */
    public function testExists()
    {
        $this->assertFalse($this->config->exists('qweewqqwe'));
        $this->assertTrue($this->config->exists('test_val_123'));
    }

    /**
     * Тест метода запроса разделителя.
     *
     * @covers \skewer\components\config\Prototype::getDelimiter
     */
    public function testGetDelimiter()
    {
        $this->assertSame('.', $this->config->getDelimiter());
    }

    /**
     * Тест перезагрузки данных.
     *
     * @covers \skewer\components\config\Prototype::reloadData
     */
    public function testReloadData()
    {
        $this->assertFalse($this->config->exists('qweewqqwe'));
        $this->config->set('qweewqqwe', 123);
        $this->assertTrue($this->config->exists('qweewqqwe'));
        $this->config->reloadData();
        $this->assertFalse($this->config->exists('qweewqqwe'));
    }
}
