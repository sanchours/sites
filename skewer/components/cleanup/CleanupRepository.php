<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 27.09.2018
 * Time: 10:38.
 */

namespace skewer\components\cleanup;

use skewer\components\cleanup\models\CleanupAnalyze;
use skewer\components\cleanup\models\CleanupScanResults;

/**
 * класс для работы с БД
 * запись, чтение, удаление.
 */
class CleanupRepository
{
    const LIMIT_COUNT = 1000;

    /**
     * @var CleanupRepository
     */
    private static $instance;

    /**
     * @return CleanupRepository
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CleanupRepository();
        }

        return self::$instance;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function clearScanResultsTable()
    {
        \Yii::$app->db->createCommand()->truncateTable(CleanupScanResults::tableName())->execute();
        CleanupHelper::printMessage('clear table ' . CleanupScanResults::tableName());
    }

    /**
     * @throws \yii\db\Exception
     */
    public function clearAnalyzeTable()
    {
        \Yii::$app->db->createCommand()->truncateTable(CleanupAnalyze::tableName())->execute();
        CleanupHelper::printMessage('clear table ' . CleanupAnalyze::tableName());
    }

    // функции для работы с таблицой cleanup_scan_results

    public function saveScanResults($aData)
    {
        if (empty($aData)) {
            return true;
        }

        $offset = 0;

        do {
            $aDataLimit = array_slice($aData, $offset, self::LIMIT_COUNT);

            if (count($aDataLimit)) {
                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        CleanupScanResults::tableName(),
                        ['module', 'action', 'file', 'assoc_data_storage'],
                        $aDataLimit
                    )->execute();
            }

            $offset += self::LIMIT_COUNT;
        } while (count($aDataLimit) == self::LIMIT_COUNT);

        return true;
    }

    public function getLimitScanData($offset, $limit = self::LIMIT_COUNT)
    {
        $aScanData = CleanupScanResults::find()
            ->orderBy('file')
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        return $aScanData;
    }

    public function getAllCountScanData()
    {
        return CleanupScanResults::find()->count();
    }

    // функции для работы с таблицой cleanup_analyze

    public function saveDataAnalyze($aData)
    {
        if (empty($aData)) {
            return true;
        }

        if (count($aData)) {
            \Yii::$app->db->createCommand()
                ->batchInsert(
                    CleanupAnalyze::tableName(),
                    ['file', 'correct', 'scanDb', 'scanFiles'],
                    $aData
                )->execute();
        }

        return true;
    }

    /**
     * @param $offset
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getLimitFilesDataAnalyze($offset)
    {
        $aData = CleanupAnalyze::find()
            ->where([
                'correct' => 0,
                'scanFiles' => 1,
            ])
            ->orderBy('file')
            ->offset($offset)
            ->limit(self::LIMIT_COUNT)
            ->asArray()
            ->all();

        return $aData;
    }

    /**
     * @return int|string
     */
    public function getCountAllRecordAnalyze()
    {
        return CleanupAnalyze::find()->count();
    }

    /**
     * @return int|string
     */
    public function getCountFilesAnalyze()
    {
        return CleanupAnalyze::find()->where(['correct' => 0, 'scanFiles' => 1])->count();
    }

    /**
     * @return int|string
     */
    public function getCountLinkDbAnalyze()
    {
        return CleanupAnalyze::find()->where(['correct' => 0, 'scanDb' => 1])->count();
    }

    /**
     * @return int|string
     */
    public function getCountCorrectRecordAnalyze()
    {
        return CleanupAnalyze::find()->where(['correct' => 1])->count();
    }
}
