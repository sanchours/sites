<?php

namespace skewer\components\cleanup\models;

/**
 * This is the model class for table "cleanup_analyze".
 *
 * @property int $id
 * @property string $file
 * @property int $correct
 * @property int $scanDb
 * @property int $scanFiles
 */
class CleanupAnalyze extends \skewer\components\ActiveRecord\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cleanup_analyze';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file', 'correct', 'scanDb', 'scanFiles'], 'required'],
            [['correct', 'scanDb', 'scanFiles'], 'integer'],
            [['file'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file' => 'File',
            'correct' => 'Correct',
            'scanDb' => 'Scan Db',
            'scanFiles' => 'Scan Files',
        ];
    }
}
