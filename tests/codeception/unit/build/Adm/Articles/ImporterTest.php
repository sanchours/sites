<?php
/**
 * Created by PhpStorm.
 * User: koval_000
 * Date: 07.11.2018
 * Time: 9:31.
 */

namespace unit\build\Adm\Articles;

use skewer\build\Adm\Tree\Importer;

class ImporterTest extends \Codeception\Test\Unit
{
    /**
     * @covers       \skewer\build\Adm\Tree\Importer::validateDataFields
     * @dataProvider getDataDataProvider
     *
     * @param array $aInData
     * @param bool $bRes
     * @param array $aResErrors
     */
    public function testValidateDataFields($aInData, $bRes, $aResErrors)
    {
        $oSectionsImporter = new Importer();
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
                    'visible' => 'Виддддимый',
                    'template' => \Yii::$app->sections->tplNew(),
                    'url' => '/test/',
                    'name' => 'test',
                    'id' => '123',
                ],
                false,
                [
                    'Неизвестный тип видимости [Виддддимый]',
                ],
            ],
            [
                [
                    'visible' => 'Виддддимый',
                    'template' => 'Шаблоннннн',
                    'url' => '/test/',
                    'name' => 'test',
                    'id' => '123',
                ],
                false,
                [
                    'Неизвестный тип видимости [Виддддимый]',
                    'Неизвестный тип шаблона [Шаблоннннн]',
                ],
            ],
            [
                [
                    'visible' => 'Виддддимый',
                    'template' => 'Шаблоннннн',
                    'url' => '',
                    'name' => '',
                    'id' => '123',
                ],
                false,
                [
                    'Неизвестный тип видимости [Виддддимый]',
                    'Неизвестный тип шаблона [Шаблоннннн]',
                    'Невозможно создать раздел с пустым названием',
                ],
            ],
            [
                [
                    'visible' => 'Виддддимый',
                    'template' => 'Шаблоннннн',
                    'url' => '/test/',
                    'name' => 'test',
                    'id' => '',
                ],
                false,
                [
                    'Неизвестный тип видимости [Виддддимый]',
                    'Неизвестный тип шаблона [Шаблоннннн]',
                    'Невозможно обновить раздел. Не указан id',
                ],
            ],
        ];
    }
}
