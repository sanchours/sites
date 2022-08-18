<?php

namespace unit\components\config;

use skewer\base\section\Parameters;
use skewer\components\config\ConfigUpdater;
use skewer\components\config\UpdateHelper;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2013-03-07 at 12:20:44.
 */
class UpdateHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var UpdateHelper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        ConfigUpdater::init();
        $this->object = new UpdateHelper();
    }

    protected function tearDown()
    {
        ConfigUpdater::revert();
    }

    /**
     * Тест параллельного выполнения двух обновлений.
     *
     * @covers \skewer\components\config\UpdateHelper::removeBuildRegistryKey
     */
    public function testRemoveSub()
    {
        $oConfig = ConfigUpdater::buildRegistry();

        $oConfig->set('testRemove', [
            'test' => [
                'test' => 1,
                'a' => 2,
            ],
        ]);

        $this->assertSame(
            1,
            $oConfig->get('testRemove.test.test'),
            'элемент не добавлен'
        );

        $oConfig->remove('testRemove.test.test');

        $this->assertNull(
            $oConfig->get('testRemove.test.test'),
            'элемент не удален'
        );

        $this->assertSame(
            2,
            $oConfig->get('testRemove.test.a'),
            'стерт родительский контейнер при удалении кодчиненного с таким же именем'
        );

        $this->assertTrue(
            $oConfig->remove([
                'testRemove',
                'test',
                'a',
            ]),
            'не верный ответ при удалении'
        );

        $this->assertFalse(
            $oConfig->remove([
                'testRemove',
                'test',
                'a',
            ]),
            'не верный ответ при удалении отсутствующего элемента'
        );

        $this->assertNull(
            $oConfig->get('testRemove.test.test'),
            'элемент не удаленб при задании имени через массив'
        );
    }

    /**
     * Тест добавления и обновления парметров.
     *
     * @covers \skewer\components\config\UpdateHelper::isSetParameter
     * @covers \skewer\components\config\UpdateHelper::updateParameter
     * @covers \skewer\components\config\UpdateHelper::addParameter
     */
    public function testUpdateParameter()
    {
        $iParent = 1;
        $sGroup = 'test1';
        $sName = 'testParam1';
        $sValue1 = 'asd123';
        $sValue2 = 'hello';

        $this->assertEmpty(Parameters::getByName($iParent, $sGroup, $sName, true), 'Тестовый параметр уже создан');

        $this->object->addParameter($iParent, $sName, $sValue1, '', $sGroup);

        $this->assertNotEmpty(Parameters::getByName($iParent, $sGroup, $sName, true), 'Тестовый параметр yt создан');

        $this->object->updateParameter($iParent, $sName, $sGroup, $sValue2);

        $aParam = Parameters::getByName($iParent, $sGroup, $sName, true);

        $this->assertSame($sValue2, $aParam['value'], 'Новое значение не сохранено');
    }

    /**
     * Тест задания параметра.
     *
     * @covers \skewer\components\config\UpdateHelper::setParameter
     */
    public function testSetParameter()
    {
        $iParent = 1;
        $sGroup = 'test4';
        $sName = 'testParam5';
        $sValue1 = 'asd123';
        $sValue2 = 'hello';

        $this->assertEmpty(Parameters::getByName($iParent, $sGroup, $sName, true), 'Тестовый параметр уже создан');

        $this->object->setParameter($iParent, $sName, $sGroup, $sValue1);

        $aParam = Parameters::getByName($iParent, $sGroup, $sName, true);
        $this->assertNotEmpty($aParam, 'Тестовый параметр не создан');
        $this->assertSame($sValue1, $aParam['value'], '1 значение не сохранено');

        $this->object->setParameter($iParent, $sName, $sGroup, $sValue2);

        $aParam = Parameters::getByName($iParent, $sGroup, $sName, true);
        $this->assertSame($sValue2, $aParam['value'], 'Новое значение не сохранено');
    }

    /**
     * Тест добавления и обновления парметров.
     *
     * @covers \skewer\components\config\UpdateHelper::isSetParameter
     */
    public function testIsSetParameter()
    {
        $iParent = 1;
        $sGroup = 'test2';
        $sName = 'testParam2';
        $sValue = 'asd123';

        $this->assertEmpty(Parameters::getByName($iParent, $sGroup, $sName, true), 'Тестовый параметр уже создан');

        $this->assertFalse(
            $this->object->isSetParameter($iParent, $sName, $sGroup),
            'Неверный ответ при проверке отсутствующего параметра'
        );

        $this->object->addParameter($iParent, $sName, $sValue, '', $sGroup);

        $this->assertNotEmpty(Parameters::getByName($iParent, $sGroup, $sName, true), 'Тестовый параметр не создан');

        $this->assertTrue(
            $this->object->isSetParameter($iParent, $sName, $sGroup),
            'Неверный ответ при проверке присутствующего параметра'
        );
    }

    /**
     * Добавить методом обновления нельзя.
     *
     * @covers \skewer\components\config\UpdateHelper::updateParameter
     */
    public function testUpdateParameterException()
    {
        $this->expectException(\skewer\components\config\UpdateException::class);

        $iParent = 1;
        $sGroup = 'test3';
        $sName = 'testParam3';
        $sValue = 'asd123';

        $this->assertEmpty(Parameters::getByName($iParent, $sGroup, $sName, true), 'Тестовый параметр уже создан');

        $this->object->updateParameter($iParent, $sName, $sGroup, $sValue);
    }
}
