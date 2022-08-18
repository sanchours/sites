<?php
/**
 * Created by PhpStorm.
 * User: kovalevr
 * Date: 28.03.2019
 * Time: 10:18.
 */

namespace unit\skewer\components\import;

use skewer\base\orm\Query;
use skewer\components\import\Api;
use yii\helpers\ArrayHelper;

class ApiTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\components\import\Api::deleteOldLogsByTplId
     *
     * @throws \yii\db\Exception
     */
    public function testDeleteOldLogsByTplId()
    {
        $iTpl_1 = 1;
        $iTpl_2 = 2;

        $iTaskId_1 = 123;
        $iTaskId_2 = 234;
        $iTaskId_3 = 456;

        // 35 дней назад
        $s35DaysAgoTime = date('Y-m-d H:m:s', strtotime('-35 days'));

        // 20 дней назад
        $s20DaysAgoTime = date('Y-m-d H:m:s', strtotime('-20 days'));

        $aTestData = [
            [$iTpl_1,  $iTaskId_1, 'start', $s35DaysAgoTime, 0, 0],
            [$iTpl_1,  $iTaskId_1, 'update_list', 'lorem', 0, 0],
            [$iTpl_1,  $iTaskId_1, 'update_list', 'lorem', 0, 0],

            [$iTpl_2,  $iTaskId_2, 'start', $s35DaysAgoTime, 0, 0],
            [$iTpl_2,  $iTaskId_2, 'update_list', 'lorem', 0, 0],
            [$iTpl_2,  $iTaskId_2, 'update_list', 'lorem', 0, 0],

            [$iTpl_1,  $iTaskId_3, 'start', $s20DaysAgoTime, 0, 0],
            [$iTpl_1,  $iTaskId_3, 'update_list', 'lorem', 0, 0],
            [$iTpl_1,  $iTaskId_3, 'update_list', 'lorem', 0, 0],
        ];

        \Yii::$app->db->createCommand()
            ->delete('import_logs')
            ->execute();

        \Yii::$app->db->createCommand()
            ->batchInsert('import_logs', ['tpl', 'task', 'name', 'value', 'list', 'saved'], $aTestData)
            ->execute();

        Api::deleteOldLogsByTplId($iTpl_1, '-30 days');

        $aImportLogs = Query::SelectFrom('import_logs')
            ->asArray()
            ->getAll();

        $aTasksId = ArrayHelper::getColumn($aImportLogs, 'task');

        $this->assertCount(6, $aTasksId);
        $this->assertFalse(in_array($iTaskId_1, $aTasksId), 'логи импорта удалены не верно');
        $this->assertTrue(in_array($iTaskId_2, $aTasksId), 'логи импорта удалены не верно');
        $this->assertTrue(in_array($iTaskId_3, $aTasksId), 'логи импорта удалены не верно');

        Api::deleteOldLogsByTplId($iTpl_2, '-30 days');

        $aImportLogs = Query::SelectFrom('import_logs')
            ->asArray()
            ->getAll();

        $aTasksId = ArrayHelper::getColumn($aImportLogs, 'task');

        $this->assertCount(3, $aTasksId);

        $this->assertFalse(in_array($iTaskId_2, $aTasksId), 'логи импорта удалены не верно');
        $this->assertTrue(in_array($iTaskId_3, $aTasksId), 'логи импорта удалены не верно');
    }
}
