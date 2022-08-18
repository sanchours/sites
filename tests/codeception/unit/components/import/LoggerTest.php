<?php

namespace unit\components\import;

use skewer\base\queue;
use skewer\base\queue\Task;
use skewer\components\import\Api;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\Log;
use skewer\components\import\Logger;

class LoggerTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\components\import\Logger::__construct
     */
    private function getLogger()
    {
        $iTpl = ImportTemplate::getNewRow([
            'card' => 1,
            'type' => Api::Type_File,
            'settings' => json_encode([]),
        ])->save();

        $iTask = queue\Api::addTask([
            'class' => '\skewer\components\import\Task',
            'priority' => queue\Task::priorityHigh,
            'resource_use' => Task::weightHigh,
            'title' => \Yii::t('import', 'task_title', 'test'),
            'parameters' => ['tpl' => (int) $iTpl],
        ]);

        return [new Logger($iTask, $iTpl), $iTask, $iTpl];
    }

    private function getParam(Logger $oLogger, $sParamName, $iTask, $iTpl)
    {
        $oLogger->save();

        $aLogs = Log::find()
            ->where('task', $iTask)
            ->where('tpl', $iTpl)
            ->where('name', $sParamName)
            ->asArray()
            ->getOne();

        return $aLogs ? $aLogs['value'] : false;
    }

    /**
     * @covers \skewer\components\import\Logger::setParam
     * @covers \skewer\components\import\Logger::incParam
     * @covers \skewer\components\import\Logger::setListParam
     */
    public function testSet()
    {
        list($oLogger, $iTask, $iTpl) = $this->getLogger();

        /* setParam */
        /* @var Logger $oLogger */
        $oLogger->setParam('param_name', 1111);

        $this->assertEquals('1111', $this->getParam($oLogger, 'param_name', $iTask, $iTpl));

        $oLogger->setParam('param_name2', 'test');
        $this->assertEquals('test', $this->getParam($oLogger, 'param_name2', $iTask, $iTpl));

        /* Именно так и работает */
        $oLogger->setParam('param_name3', ['test' => 1111]);
        $this->assertEquals('1111', $this->getParam($oLogger, 'param_name3', $iTask, $iTpl));

        /* incParam */

        $oLogger->incParam('param_name4');
        $this->assertEquals(1, $this->getParam($oLogger, 'param_name4', $iTask, $iTpl));

        $oLogger->incParam('param_name4');
        $oLogger->incParam('param_name4');
        $this->assertEquals(3, $this->getParam($oLogger, 'param_name4', $iTask, $iTpl));

        $oLogger->setParam('param_name4', 8);
        $this->assertEquals(8, $this->getParam($oLogger, 'param_name4', $iTask, $iTpl));
        $oLogger->incParam('param_name4');
        $this->assertEquals(9, $this->getParam($oLogger, 'param_name4', $iTask, $iTpl));

        /* setListParam */

        $oLogger->setListParam('name', 2);
        $oLogger->setListParam('name', 2);
        $oLogger->setListParam('name', 4);

        $oLogger->save();
        $aLogs = Log::find()
            ->where('task', $iTask)
            ->where('tpl', $iTpl)
            ->where('name', 'name')
            ->asArray()
            ->getAll();

        $aRes = [];
        foreach ($aLogs as $val) {
            $aRes[] = $val['value'];
        }
        sort($aRes);

        $this->assertEquals(['2', '2', '4'], $aRes);
    }

    /**
     * @covers \skewer\components\import\Logger::save
     * @covers \skewer\components\import\Logger::setSaved
     */
    public function testSave()
    {
        list($oLogger, $iTask, $iTpl) = $this->getLogger();

        /* @var Logger $oLogger */
        $oLogger->setParam('param_name1', 'val1');
        $oLogger->setParam('param_name2', 'val2');

        $oLogger->setListParam('param_name3', 'val3');
        $oLogger->setListParam('param_name3', 'val4');

        $oLogger->save();
        $aLogs = Log::find()
            ->where('task', $iTask)
            ->where('tpl', $iTpl)
            ->asArray()
            ->getAll();

        $aRes = [];
        foreach ($aLogs as $val) {
            if ($val['list']) {
                $aRes[$val['name']][] = $val['value'];
            } else {
                $aRes[$val['name']] = $val['value'];
            }
        }

        $this->assertEquals($aRes['param_name1'], 'val1');
        $this->assertEquals($aRes['param_name2'], 'val2');
        $this->assertEquals($aRes['param_name3'], ['val3', 'val4']);

        $oLogger->save();

        $oLogger = new Logger($iTask, $iTpl);
        $oLogger->setListParam('param_name4', 'val5');
        $oLogger->save();
        $aLogs = Log::find()
            ->where('task', $iTask)
            ->where('tpl', $iTpl)
            ->where('name', 'param_name4')
            ->asArray()
            ->getAll();

        $this->assertEquals(count($aLogs), 1);
        $this->assertEquals($aLogs[0]['value'], 'val5');

        $oLogger = new Logger($iTask, $iTpl);
        $oLogger->setSaved(['param_name5']);

        $oLogger->setListParam('param_name5', 'val5');
        $oLogger->save();

        $oLogger = new Logger($iTask, $iTpl);
        $oLogger->setSaved(['param_name5']);

        $oLogger->setListParam('param_name5', 'val6');
        $oLogger->save();
        $aLogs = Log::find()
            ->where('task', $iTask)
            ->where('tpl', $iTpl)
            ->where('name', 'param_name5')
            ->asArray()
            ->order('value')
            ->getAll();

        $this->assertEquals(count($aLogs), 2);
        $this->assertEquals($aLogs[0]['value'], 'val5');
        $this->assertEquals($aLogs[1]['value'], 'val6');
    }
}
