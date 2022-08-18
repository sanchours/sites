<?php

namespace unit\components\Cron;

use skewer\base\SysVar;
use skewer\components\Cron\Api;

/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.07.2016
 * Time: 11:12
 * To run this test use: codecept run codeception/unit/components/Cron/ApiTest.php.
 */
class ApiTest extends \Codeception\Test\Unit
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        SysVar::set('ScheduleLastStartTimeTasks', "{'1':1473600005}");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function baseWorkProvider()
    {
        return [
            /*Тесты на конкретное время*/
            [
                [
                    'task' => [
                          'c_min' => '00',
                          'c_hour' => '12',
                         'c_day' => '*',
                          'c_month' => '*',
                          'c_dow' => '*',
                          'status' => '1',
                    ],
                    'time' => [
                          0 => '00',
                          1 => '12',
                          2 => '12',
                          3 => '09',
                          4 => '1',
                    ],
                ],
                true,
            ],
            [
                [
                    'task' => [
                        'c_min' => '00',
                        'c_hour' => '12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '05',
                        1 => '12',
                        2 => '12',
                        3 => '09',
                        4 => '1',
                    ],
                ],
                false,
            ],
            /*Тест на интервал*/
            [
                [
                    'task' => [
                       'c_min' => '05',
                        'c_hour' => '10-12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '05',
                        1 => '11',
                        2 => '05',
                        3 => '09',
                        4 => '1',
                    ],
                ],
                true,
            ],
            [
                [
                    'task' => [
                        'c_min' => '05',
                        'c_hour' => '10-12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '05',
                        1 => '12',
                        2 => '12',
                        3 => '09',
                        4 => '1',
                    ],
                ],
                false,
            ],
            /*Тест каждые*/
            [
                [
                    'task' => [
                        'id' => '1',
                        'c_min' => '*/20',
                        'c_hour' => '12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '00',
                        1 => '12',
                        2 => '05',
                        3 => '09',
                        4 => '1',
                    ],
                    'set_time' => Api::timestamp(strtotime(date('Y') . '-09-05 10:00:00')),
                ],
                true,
            ],
            /*Типо прошло 5 минут*/
            [
                [
                    'task' => [
                        'id' => '1',
                        'c_min' => '*/20',
                        'c_hour' => '12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '10',
                        1 => '12',
                        2 => '05',
                        3 => '09',
                        4 => '1',
                    ],
                    'set_time' => Api::timestamp(strtotime(date('Y') . '-09-05 12:05:00')),
                ],
                false,
            ],
            /*Прошло еще 16 минут*/
            [
                [
                    'task' => [
                        'id' => '1',
                        'c_min' => '*/20',
                        'c_hour' => '12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '26',
                        1 => '12',
                        2 => '05',
                        3 => '09',
                        4 => '1',
                    ],
                    'set_time' => Api::timestamp(strtotime(date('Y') . '-09-05 12:05:00')),
                ],
                true,
            ],
            [
                [
                    'task' => [
                        'id' => '1',
                        'c_min' => '*/20',
                        'c_hour' => '12',
                        'c_day' => '*',
                        'c_month' => '*',
                        'c_dow' => '*',
                        'status' => '1',
                    ],
                    'time' => [
                        0 => '00',
                        1 => '12',
                        2 => '05',
                        3 => '09',
                        4 => '1',
                    ],
                    'set_time' => Api::timestamp(strtotime(date('Y') . '-09-05 12:00:00')),
                ],
                false,
            ],
        ];
    }

    /**
     * @covers \skewer\components\Cron\Api::runTask
     * @dataProvider baseWorkProvider
     *
     * @param $aInput - время текущее и время запуска задания
     * @param $bOutput - ожидаемый результат bool
     */
    public function testRunTask($aInput, $bOutput)
    {
        /*Установка времени прошлого запуска. Нужна для теста "Запуск каждые N"*/

        if (isset($aInput['set_time'])) {
            SysVar::set('ScheduleLastStartTimeTasks', json_encode(['1' => $aInput['set_time']]));
        }

        $bResult = Api::runTask($aInput['task'], $aInput['time']);

        if ($bOutput) {
            $this->assertTrue($bResult, '111');
        } else {
            $this->assertFalse($bResult, '222');
        }
    }
}
