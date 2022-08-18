<?php

namespace unit\components\import;

use skewer\components\import\Api;
use skewer\components\import\Config;
use skewer\components\import\provider\Xls;

class ProviderTest extends \Codeception\Test\Unit
{
    const path = 'tests/codeception/unit/components/import/files/';

    private function getProvider($aData = [])
    {
        $oConfig = new Config();
        $oConfig->setData($aData);

        $this->providerTest($aData);

        return Api::getProvider($oConfig);
    }

    /**
     * @covers \skewer\components\import\Api::getProvider
     */
    public function testErrorGetProvider()
    {
        $this->expectException(\Exception::class);

        $this->getProvider(['provider_type' => 'fail']);
    }

    /**
     * Не задан файл.
     *
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::fail
     */
    public function testErrorNoFile()
    {
        $this->expectException(\Exception::class);

        $this->providerTest(['provider_type' => Api::ptCSV, 'type' => Api::Type_Path, 'source' => '']);
    }

    /**
     * Не найден файл.
     *
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::fail
     */
    public function testErrorNoExistFile()
    {
        $this->expectException(\Exception::class);

        $this->providerTest(['provider_type' => Api::ptCSV, 'type' => Api::Type_Path, 'source' => '1.txt']);
    }

    /**
     * Неверный формат
     *
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::validateFormat
     * @covers \skewer\components\import\provider\Prototype::fail
     */
    public function testErrorValidate()
    {
        $this->expectException(\Exception::class);

        $this->providerTest(['provider_type' => Api::ptCSV, 'type' => Api::Type_Path, 'source' => static::path . 'test.txt']);
    }

    /**
     * Неверная кодировка.
     *
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::checkCoding
     * @covers \skewer\components\import\provider\Prototype::fail
     */
    public function testErrorCoding()
    {
        $this->expectException(\Exception::class);

        $aData = ['provider_type' => Api::ptCSV, 'type' => Api::Type_Path,
            'source' => static::path . '1.csv', 'coding' => Api::windows, ];
        $this->providerTest($aData);
    }

    /**
     * Общий тест на провайдер
     *
     * @param array $aData
     */
    private function providerTest($aData = [])
    {
        $oConfig = new Config();
        $oConfig->setData($aData);

        $oProvider = Api::getProvider($oConfig);

        /* Разрешенные расширения */
        $this->assertInternalType('array', $oProvider->getAllowedExtension());
        $this->assertTrue(count($oProvider->getAllowedExtension()) > 0);

        /* Доступ на чтение */
        $this->assertTrue($oProvider->canRead());

        /* Данные конфига */
        foreach ($aData as $k => $v) {
            if ($k !== 'row') {//хак на xls
                $this->assertEquals($oProvider->getConfigVal($k), $v);
            }
        }

        $oProvider->setConfigVal('test', 123);
        $oProvider->setConfigVal('test2', 'test');
        $oProvider->setConfigVal('test3', ['1' => 2]);

        $this->assertEquals($oProvider->getConfigVal('test'), 123);
        $this->assertEquals($oProvider->getConfigVal('test2'), 'test');
        $this->assertEquals($oProvider->getConfigVal('test3'), ['1' => 2]);

        /* Пример */
        $this->assertInternalType('string', $oProvider->getExample());

        /* Массив 1 товара для инфы */
        $this->assertInternalType('array', $oProvider->getInfoRow());

        /* Параметры */
        $this->assertInternalType('array', $oProvider->getParameters());
        foreach ($oProvider->getParameters() as $k => $v) {
            if (isset($aData[$k])) {
                if ($k !== 'delimiter') { //хак на csv
                    $this->assertAttributeEquals($aData[$k], $k, $oProvider);
                }
            } else {
                $this->assertAttributeEquals($v['default'], $k, $oProvider);
            }
        }

        /** Кодировка */
        $s = $oProvider->getPureString();
        $code = Api::detect_encoding($s);
        $this->assertEquals($code, $aData['coding'] ?? Api::utf);

        $oProvider->beforeExecute();
        $oProvider->getRow();
        $oProvider->afterExecute();
    }

    /**
     * @covers \skewer\components\import\provider\Csv::init
     * @covers \skewer\components\import\provider\Csv::beforeExecute
     * @covers \skewer\components\import\provider\Csv::getRow
     * @covers \skewer\components\import\provider\Csv::loadDelimiter
     * @covers \skewer\components\import\provider\Csv::getExample
     * @covers \skewer\components\import\provider\Csv::getInfoRow
     * @covers \skewer\components\import\provider\Csv::getPureString
     *
     * @covers \skewer\components\import\Api::getProvider
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::getAllowedExtension
     * @covers \skewer\components\import\provider\Prototype::canRead
     * @covers \skewer\components\import\provider\Prototype::setConfigVal
     * @covers \skewer\components\import\provider\Prototype::getConfigVal
     * @covers \skewer\components\import\provider\Prototype::getExample
     * @covers \skewer\components\import\provider\Prototype::getInfoRow
     * @covers \skewer\components\import\provider\Prototype::getParameters
     * @covers \skewer\components\import\provider\Prototype::initParam
     * @covers \skewer\components\import\provider\Prototype::checkCoding
     */
    public function testCsv()
    {
        $aData = ['provider_type' => Api::ptCSV, 'type' => Api::Type_Path,
            'source' => static::path . '1.csv', 'coding' => Api::utf, ];

        $oProvider = $this->getProvider($aData);

        $aRow = $oProvider->getInfoRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '113724');

        $s = $oProvider->getExample();
        $this->assertEquals(mb_strpos($s, '113724'), 0);

        /* Пропуск строки */
        $aData['skip_row'] = 2;
        $oProvider = $this->getProvider($aData);

        $aRow = $oProvider->getInfoRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '114127');

        $s = $oProvider->getExample();
        $this->assertEquals(mb_strpos($s, '114127'), 0);

        /* Разделитель */
        $aData['delimiter'] = '*';
        $oProvider = $this->getProvider($aData);
        $this->assertAttributeEquals('*', 'delimiter', $oProvider);

        $aData['delimiter'] = 'tab';
        $oProvider = $this->getProvider($aData);
        $this->assertAttributeEquals(chr(9), 'delimiter', $oProvider);

        $aData['delimiter'] = '123';
        $oProvider = $this->getProvider($aData);
        $this->assertAttributeEquals('1', 'delimiter', $oProvider);

        /* Цикл */
        $aData['delimiter'] = ';';
        $oProvider = $this->getProvider($aData);

        /* skip_row = 2 */
        $oProvider->beforeExecute();
        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '114127'); //3-я строка

        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '113334'); //4-я строка

        $oProvider->afterExecute();
        $iTell = $oProvider->getConfigVal('tell');

        $aData['tell'] = $iTell; //читаем с места, где закончили
        $oProvider = $this->getProvider($aData);

        $oProvider->beforeExecute();
        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '114116'); //5-я строка

        $oProvider->afterExecute();

        /* Тест на конец */
        $aData['skip_row'] = 0;
        $aData['source'] = static::path . '2.csv';
        $oProvider = $this->getProvider($aData);
        $oProvider->beforeExecute();
        $oProvider->getRow();
        $oProvider->getRow();
        $this->assertFalse($oProvider->getRow());
        $oProvider->afterExecute();

        /* Если строк в файле меньше, чем пропущено */
        $aData['skip_row'] = 3;
        $oProvider = $this->getProvider($aData);
        $this->assertEquals($oProvider->getExample(), '');
        $this->assertEquals($oProvider->getInfoRow(), []);
        $oProvider->beforeExecute();
        $this->assertFalse($oProvider->getRow());
        $oProvider->afterExecute();
    }

    /**
     * @covers \skewer\components\import\provider\Xls::init
     * @covers \skewer\components\import\provider\Xls::beforeExecute
     * @covers \skewer\components\import\provider\Xls::getRow
     * @covers \skewer\components\import\provider\Xls::getExample
     * @covers \skewer\components\import\provider\Xls::readRow
     * @covers \skewer\components\import\provider\Xls::getInfoRow
     * @covers \skewer\components\import\provider\Xls::getPureString
     *
     * @covers \skewer\components\import\Api::getProvider
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::getAllowedExtension
     * @covers \skewer\components\import\provider\Prototype::canRead
     * @covers \skewer\components\import\provider\Prototype::setConfigVal
     * @covers \skewer\components\import\provider\Prototype::getConfigVal
     * @covers \skewer\components\import\provider\Prototype::getExample
     * @covers \skewer\components\import\provider\Prototype::getInfoRow
     * @covers \skewer\components\import\provider\Prototype::getParameters
     * @covers \skewer\components\import\provider\Prototype::initParam
     * @covers \skewer\components\import\provider\Prototype::checkCoding
     */
    public function testXls()
    {
        $aData = ['provider_type' => Api::ptXLS, 'type' => Api::Type_Path,
            'source' => static::path . '1.xlsx', 'coding' => Api::utf, ];

        $oProvider = $this->getProvider($aData);

        $aRow = $oProvider->getInfoRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '113724');

        $s = $oProvider->getExample();
        $this->assertEquals(mb_strpos($s, '113724'), 0);

        /* Пропуск строки */
        $aData['skip_row'] = 2;
        $oProvider = $this->getProvider($aData);

        $aRow = $oProvider->getInfoRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '114127');

        $s = $oProvider->getExample();
        $this->assertEquals(mb_strpos($s, '114127'), 0);

        /** Цикл */
        $iRowCount = 7;
        $aData['skip_row'] = 2;
        $aData['row_count'] = $iRowCount;
        $oProvider = $this->getProvider($aData);

        $oProvider->beforeExecute();
        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals(count($aRow), $iRowCount);
        $this->assertEquals($aRow[0], '114127'); //1-я строка

        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals(count($aRow), $iRowCount);
        $this->assertEquals($aRow[0], '113334'); //2-я строка

        $oProvider->afterExecute();

        $aData['row'] = 5; //читаем с места
        $oProvider = $this->getProvider($aData);

        $oProvider->beforeExecute();
        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '114116'); //5-я строка

        $oProvider->afterExecute();

        /* Конец */
        $aData['row'] = 0;
        $aData['skip_row'] = 0;
        $aData['source'] = static::path . '2.xlsx';
        $oProvider = $this->getProvider($aData);
        $oProvider->beforeExecute();
        $oProvider->getRow();
        $oProvider->getRow();
        $this->assertFalse($oProvider->getRow());
        $oProvider->afterExecute();

        /* Условие пропуска */
        $aData['row'] = 0;
        $aData['skip_row'] = 0;
        $aData['source'] = static::path . '3.xlsx';
        $oProvider = $this->getProvider($aData);
        $oProvider->beforeExecute();
        $oProvider->getRow();
        $oProvider->getRow();
        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow[0], '114129');
        $this->assertFalse($oProvider->getRow());
        $oProvider->afterExecute();

        /* Если строк в файле меньше, чем пропущено */
        $aData['skip_row'] = 3;
        $aData['source'] = static::path . '2.xlsx';
        $oProvider = $this->getProvider($aData);
        $this->assertEquals($oProvider->getExample(), '');
        $this->assertEquals($oProvider->getInfoRow(), []);
        $oProvider->beforeExecute();
        $this->assertFalse($oProvider->getRow());
        $oProvider->afterExecute();

        /* Лимит на чтение */
        $aData['source'] = static::path . '203.xlsx';
        $aData['skip_row'] = 0;
        $oProvider = $this->getProvider($aData);
        $oProvider->beforeExecute();
        $i = 0;
        while ($aRow = $oProvider->getRow() !== false) {
            ++$i;
        }
        $this->assertEquals($i, Xls::Limit);
        $this->assertFalse($oProvider->canRead());
        $oProvider->afterExecute();
    }

    /**
     * Тест формата CommerceML на одиночное значение в справочнике и множество картинок для одного товара.
     *
     * @covers \skewer\components\import\provider\CommerceMLImport::init
     * @covers \skewer\components\import\provider\CommerceMLImport::beforeExecute
     * @covers \skewer\components\import\provider\CommerceMLImport::getRow
     * @covers \skewer\components\import\provider\CommerceMLImport::getExample
     * @covers \skewer\components\import\provider\CommerceMLImport::getInfoRow
     * @covers \skewer\components\import\provider\CommerceMLImport::getPureString
     *
     * @covers \skewer\components\import\Api::getProvider
     * @covers \skewer\components\import\provider\Prototype::__construct
     * @covers \skewer\components\import\provider\Prototype::getAllowedExtension
     * @covers \skewer\components\import\provider\Prototype::canRead
     * @covers \skewer\components\import\provider\Prototype::setConfigVal
     * @covers \skewer\components\import\provider\Prototype::getConfigVal
     * @covers \skewer\components\import\provider\Prototype::getExample
     * @covers \skewer\components\import\provider\Prototype::getInfoRow
     * @covers \skewer\components\import\provider\Prototype::getParameters
     * @covers \skewer\components\import\provider\Prototype::initParam()
     * @covers \skewer\components\import\provider\Prototype::checkCoding()
     */
    public function testCommerceMLSingleDictAndMoreImages()
    {
        $aData = ['provider_type' => Api::ptCommerceMLImport, 'type' => Api::Type_Path,
                  'source' => static::path . '1.xml', 'coding' => Api::utf, ];

        /** @var \skewer\components\import\provider\CommerceMLImport $oProvider */
        $oProvider = $this->getProvider($aData);

        $sExample = $oProvider->getExample();
        $this->assertFalse(mb_strpos($sExample, 'Ид:8ab605cd-7a5b-11e5-825f-5c93a2fbdb9a') === false);

        $aRow = $oProvider->getInfoRow();
        $this->assertInternalType('array', $aRow);

        $oProvider->beforeExecute();

        // Тест первой записи: Несколько картинок и один справочник
        $aRow = $oProvider->getRow();
        $this->assertInternalType('array', $aRow);
        $this->assertEquals($aRow['Ид'], '8ab605cd-7a5b-11e5-825f-5c93a2fbdb9a');
        $this->assertEquals($aRow['Картинка'], '1,3,2');
        $this->assertEquals($aRow['field_ВидНоменклатуры'], 'Конфеты весовые');
        $this->assertEquals($aRow['field_ТипНоменклатуры'], 'Товар');
        $this->assertEquals($aRow['dict_420ea1b9-eeac-11e5-8280-5c93a2fbdb9a'], 'Вафли');

        // Тест второй записи: Одна картинка и два справочника
        $aRow = $oProvider->getRow();
        $this->assertEquals($aRow['Картинка'], '1');
        $this->assertEquals($aRow['dict_420ea1b9-eeac-11e5-8280-5c93a2fbdb9a'], 'Вафли');
        $this->assertEquals($aRow['dict_420ea1c9-eeac-11e5-8280-5c93a2fbdb9a'], 'Крем');

        $oProvider->afterExecute();
    }
}
