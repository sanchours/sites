<?php

namespace skewer\build\Adm\Testing\components;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $path_id
 * @property int $check
 */
class ManualTestEntity extends ActiveRecord
{
    public static function tableName()
    {
        return 'manual_test';
    }

    public static function getByPathId($pathId)
    {
        return self::find()->where(['path_id' => $pathId])->one();
    }

    public function rules()
    {
        return [
            [['path_id', 'check'], 'required'],
            [['check'], 'boolean'],
            [['path_id'], 'string', 'max' => 255],
        ];
    }
}
