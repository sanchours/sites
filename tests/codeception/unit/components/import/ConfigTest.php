<?php

namespace unit\components\import;

use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\Config;

class ConfigTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\components\import\Config::__construct
     */
    public function testConstruct()
    {
        $oConfig = new Config();

        /* Пустой конфиг */
        $this->assertEquals($oConfig->getData(), []);
        $this->assertEquals($oConfig->getJsonData(), '[]');

        $oTpl = new ImportTemplateRow();

        $aData = [
            'paramName1' => 'paramValue1',
            'paramName2' => 11111,
            'paramName3' => ['param' => 'value', 'param2' => 4],
        ];
        $oTpl->settings = json_encode($aData);

        $oConfig = new Config($oTpl);

        $aReturnData = $oConfig->getData();
        foreach ($aData as $k => $v) {
            $this->assertArrayHasKey($k, $aReturnData);
            $this->assertEquals($v, $aReturnData[$k]);
            $this->assertEquals($oConfig->getParam($k), $aReturnData[$k]);
        }
    }

    /**
     * @covers \skewer\components\import\Config::__construct
     */
    public function testErrorConstruct()
    {
        $this->expectException(\Exception::class);

        $oTpl = new ImportTemplateRow();
        $oTpl->settings = '[(1234skewer!';

        new Config($oTpl);
    }

    private function getConfig($aData = [])
    {
        $oTpl = new ImportTemplateRow();
        $oTpl->settings = json_encode($aData);

        return new Config($oTpl);
    }

    /**
     * @covers \skewer\components\import\Config::setData
     * @covers \skewer\components\import\Config::getData
     * @covers \skewer\components\import\Config::getJsonData
     */
    public function testSetData()
    {
        $oConfig = $this->getConfig();

        $aData = [
            'paramName1' => 'paramValue1',
            'paramName2' => 11111,
            'paramName3' => ['param' => 'value', 'param2' => 4],
        ];

        $sJson = json_encode($aData);

        $oConfig->setData($aData);

        $aReturnData = $oConfig->getData();
        foreach ($aData as $k => $v) {
            $this->assertArrayHasKey($k, $aReturnData);
            $this->assertEquals($v, $aReturnData[$k]);
            $this->assertEquals($oConfig->getParam($k), $aReturnData[$k]);
        }

        $this->assertEquals($oConfig->getJsonData(), $sJson);
    }

    /**
     * @covers \skewer\components\import\Config::setFields
     * @covers \skewer\components\import\Config::setFieldsParam
     * @covers \skewer\components\import\Config::clearFields
     */
    public function testFields()
    {
        $oConfig = $this->getConfig();

        $oConfig->setFields([]);
        $this->assertEquals($oConfig->getParam('fields'), '');

        $aData = [
            'field_15' => '123',
            'field_19' => '456',
            'field_21' => '456',
            'field11_17' => '789',
            'type_15' => 'test',
            'type_19' => 'test2',
        ];

        $oConfig->setFields($aData);

        $aFields = $oConfig->getParam('fields');

        $this->assertArrayNotHasKey('field11_17', $aFields);
        $this->assertArrayNotHasKey('11_17', $aFields);
        $this->assertArrayNotHasKey('type_19', $aFields);

        foreach ($aFields as $k => $v) {
            $this->assertArrayHasKey('field_' . $k, $aData);
            $this->assertArrayHasKey('type_' . $k, $aData);
            $this->assertEquals($v['importFields'], $aData['field_' . $k]);
            $this->assertEquals($oConfig->getParam('fields.' . $k . '.importFields'), $aData['field_' . $k]);
            $this->assertEquals($oConfig->getParam('fields.' . $k . '.type'), $aData['type_' . $k]);
        }

        $this->assertEquals($oConfig->getParam('fields.15.type'), 'test');
        $this->assertEquals($oConfig->getParam('fields.15.importFields'), '123');
        $this->assertEquals($oConfig->getParam('fields.15.name'), '15');
        $this->assertEquals($oConfig->getParam('fields.21'), '');

        $this->assertEquals($oConfig->getParam('field11_17'), '');

        /** Параметры */
        $aParams = [
            'params_15:param1' => '123',
            'params_15:param2' => '456',
            'params_17:param3' => '789',
        ];

        $oConfig->setFieldsParam($aParams);

        $this->assertEquals($oConfig->getParam('fields.15.params.param1'), '123');
        $this->assertEquals($oConfig->getParam('fields.15.params.param2'), '456');
        $this->assertEquals($oConfig->getParam('fields.17.params.param3'), '');

        /* Чистка */
        $oConfig->clearFields();
        $this->assertEquals($oConfig->getParam('fields'), '');
    }

    /**
     * @covers \skewer\components\import\Config::setParam
     * @covers \skewer\components\import\Config::getParam
     */
    public function testGetParam()
    {
        $oConfig = $this->getConfig();

        $oConfig->setParam('param1', 123);
        $oConfig->setParam('param2', '12dsd');
        $oConfig->setParam('param3', ['test' => 1]);

        $this->assertEquals($oConfig->getParam('param1'), 123);
        $this->assertEquals($oConfig->getParam('param2'), '12dsd');
        $this->assertEquals($oConfig->getParam('param3'), ['test' => 1]);

        $this->assertEquals($oConfig->getParam('param4'), '');
        $this->assertEquals($oConfig->getParam('param4', 'test'), 'test');

        $this->assertEquals($oConfig->getParam('param1', 'test'), 123);
    }
}
