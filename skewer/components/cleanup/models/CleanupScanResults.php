<?php

namespace skewer\components\cleanup\models;

/**
 * This is the model class for table "cleanup_scan_results".
 *
 * @property int $id
 * @property string $module
 * @property string $action
 * @property string $file
 * @property string $assoc_data_storage
 */
class CleanupScanResults extends \skewer\components\ActiveRecord\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cleanup_scan_results';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module', 'action', 'file', 'assoc_data_storage'], 'required'],
            [['file', 'assoc_data_storage'], 'string'],
            [['module'], 'string', 'max' => 150],
            [['action'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module' => 'Module',
            'action' => 'Action',
            'file' => 'File',
            'assoc_data_storage' => 'Assoc Data Storage',
        ];
    }
}
