<?php

namespace unit\components\import;

use skewer\base\queue;
use skewer\components\catalog;
use skewer\components\import\Api;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\field\Money;
use skewer\components\import\Task;

class MoneyFieldTest extends \Codeception\Test\Unit
{
    public function providerMoney()
    {
        return [
            ['123,456,789', 123456789.0, 0],
            ['123.456.789', 123456789.0, 0],

            ['123456.789', 123456.79, 0],
            ['123456,789', 123456.79, 0],

            ['123,456,789.123', 123456789.12, 0],
            ['123.456.789,123', 123456789.12, 0],

            ['123', 123.0, 0],
            ['123руб.', 123.0, 0],
            ['123руб', 123.0, 0],
            ['123 руб', 123.0, 0],
            ['123$', 123.0, 0],

            ['123.125', 123.12, 0],
            ['123.', 123.0, 0],
            ['0.123', 0.12, 0],
            ['.123', 0.12, 0],

            ['.', 0.0, 0],
            ['', 0.0, 0],
            [' ', 0.0, 0],
            ['      ', 0.0, 0],

            ['1000', 113000.0, 113],
            ['1020.34', 115298.42, 113],
        ];
    }

    /**
     * @covers \skewer\components\import\field\Money
     * @dataProvider providerMoney
     *
     * @param mixed $sIn
     * @param mixed $fOut
     * @param mixed $fMarkUp
     */
    public function testMoney($sIn, $fOut, $fMarkUp)
    {
        $iTpl = ImportTemplate::getNewRow([
            'card' => catalog\Card::DEF_BASE_CARD,
            'type' => Api::Type_File,
            'settings' => json_encode(['fields.price.params' => ['markup' => $fMarkUp]]),
        ])->save();

        $iTask = queue\Api::addTask([
            'class' => '\skewer\components\import\Task',
            'priority' => queue\Task::priorityHigh,
            'resource_use' => Task::weightLow,
            'title' => \Yii::t('import', 'task_title', 'test'),
            'parameters' => ['tpl' => (int) $iTpl],
        ]);

        $oTask = queue\Api::getTaskById($iTask);

        $oTask->init();

        $o = new Money(['val'], 'price', $oTask);

        $o->loadData(['val' => $sIn]);

        $this->assertSame($fOut, $o->getValue());
    }
}
