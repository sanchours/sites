<?php
/**
 * Created by PhpStorm.
 * User: koval_000
 * Date: 07.11.2018
 * Time: 9:52.
 */

namespace unit\build\Tool\SeoGen\importer;

use skewer\build\Tool\SeoGen\importer\Prototype;

class PrototypeTest extends \Codeception\Test\Unit
{
    /**
     * @covers       \skewer\build\Tool\SeoGen\importer\Prototype::validateDataFields
     * @dataProvider getDataDataProvider
     *
     * @param array $aInData
     * @param bool $bRes
     * @param array $aResErrors
     */
    public function testValidateDataFields($aInData, $bRes, $aResErrors)
    {
        $oSectionsImporter = new TestImporter();
        $aErrors = [];

        $this->assertSame($bRes, $oSectionsImporter->validateDataFields($aInData, $aErrors));
        $this->assertSame($aResErrors, $aErrors);
    }

    public function getDataDataProvider()
    {
        return [
            [
                [
                    'visible' => 'Видимый',
                    'template' => \Yii::$app->sections->tplNew(),
                    'url' => '/test/',
                    'name' => 'test',
                    'id' => '123',
                ],
                true,
                [],
            ],
            [
                [
                    'visible' => 'Видимый',
                    'template' => \Yii::$app->sections->tplNew(),
                    'url' => '',
                    'name' => 'test',
                    'id' => '123',
                ],
                false,
                [
                    'Невозможно обновить запись. Не задан урл',
                ],
            ],
        ];
    }
}

class TestImporter extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'test';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'тест';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableSeoEntity()
    {
        return [];
    }
}
