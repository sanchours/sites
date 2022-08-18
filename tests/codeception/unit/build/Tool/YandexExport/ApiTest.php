<?php

namespace unit\build\Tool\YandexExport;

use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\GoodsSelector;

/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.07.2016
 * Time: 11:12
 * To run this test use: codecept run codeception/unit/build/Tool/YandexExport/ApiTest.php.
 */
class ApiTest extends \Codeception\Test\Unit
{
    /**
     * @var \skewer\components\redirect\Api
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        /*Установим модуль яндекс маркета*/
        $installer = new \skewer\components\config\installer\Api();
        if (!$installer->isInstalled('YandexExport', 'Tool')) {
            $installer->install('YandexExport', 'Tool');
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \skewer\build\Tool\YandexExport\Api::setDataForSectionGoods
     */
    public function testSetDataForSectionGoods()
    {
        $aYandexParams = [
            'in_yandex',
            'pickup',
            'available',
            'store',
            'delivery',
            'warranty',
            'adult',
        ];

        /*Список товарных разделов в нулевой сборке*/
        $aSections = [
            282,
            283,
            284,
            289,
            290,
            291,
            292,
        ];

        $this->setAll($aSections, $aYandexParams, 0);

        /*Проверяем установленность*/
        foreach ($aSections as $iSection) {
            $query = GoodsSelector::getList4Section($iSection);

            while ($aGoods = $query->parseEach()) {
                $oGoodsRow = GoodsRow::get($aGoods['id'])->getData();

                foreach ($aYandexParams as $param) {
                    $this->assertEquals('0', $oGoodsRow[$param], 'ERROR. Problem with disabling all params');
                }
            }
        }

        /*Включаем в 1 разделе все галки*/
        $aData = [];

        foreach ($aYandexParams as $param) {
            $aData[$param] = '1';
        }

        $iRandSection = random_int(0, count($aSections) - 1);

        $iRandSection = $aSections[$iRandSection];

        /*Включили рандомному разделу все галки*/
        \skewer\build\Tool\YandexExport\Api::setDataForSectionGoods($iRandSection, $aData);

        /*Проверяем установленность*/
        foreach ($aSections as $iSection) {
            $query = GoodsSelector::getList4Section($iSection);

            while ($aGoods = $query->parseEach()) {
                $oGoodsRow = GoodsRow::get($aGoods['id'])->getData();

                foreach ($aYandexParams as $param) {
                    if ($iSection == $iRandSection) {
                        $this->assertEquals('1', $oGoodsRow[$param], 'ERROR. Problem with enabling all params in random section');
                    } else {
                        $this->assertEquals('0', $oGoodsRow[$param], 'ERROR. Problem with enabling all params in other sections');
                    }
                }
            }
        }

        /*Снова отключаем все параметры*/
        $this->setAll($aSections, $aYandexParams, 0);

        $aData = [
            'in_yandex' => 1,
            'pickup' => 0,
            'available' => 1,
            'store' => 0,
            'delivery' => 1,
            'warranty' => 0,
            'adult' => 1,
        ];

        $iRandSection = random_int(0, count($aSections) - 1);

        $iRandSection = $aSections[$iRandSection];

        /*Включили рандомному разделу все галки*/
        \skewer\build\Tool\YandexExport\Api::setDataForSectionGoods($iRandSection, $aData);

        /*Проверяем установленность*/
        foreach ($aSections as $iSection) {
            $query = GoodsSelector::getList4Section($iSection);

            while ($aGoods = $query->parseEach()) {
                $oGoodsRow = GoodsRow::get($aGoods['id'])->getData();

                foreach ($aYandexParams as $param) {
                    if ($iSection == $iRandSection) {
                        $this->assertEquals($aData[$param], $oGoodsRow[$param], 'ERROR. Incorrect value in random section');
                    } else {
                        $this->assertEquals('0', $oGoodsRow[$param], 'ERROR. Incorrect value in other section');
                    }
                }
            }
        }
    }

    private function setAll($aSections, $aYandexParams, $iValue)
    {
        $aData = [];

        foreach ($aYandexParams as $param) {
            $aData[$param] = $iValue;
        }

        /*Установим все значения яндексмаркета всем товарам в 0*/
        foreach ($aSections as $iSection) {
            \skewer\build\Tool\YandexExport\Api::setDataForSectionGoods($iSection, $aData);
        }
    }
}
