<?php

namespace skewer\build\Adm\Order\model;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "orders_change_status".
 *
 * @property int $id
 * @property string $change_date
 * @property int $id_order
 * @property int $id_old_status
 * @property int $id_new_status
 */
class ChangeStatus extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_change_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['change_date', 'id_order', 'id_old_status', 'id_new_status'], 'required'],
            [['change_date'], 'safe'],
            [['id_order', 'id_old_status', 'id_new_status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'change_date' => 'Change Date',
            'id_order' => 'Id Order',
            'id_old_status' => 'Old Status',
            'id_new_status' => 'New Status',
        ];
    }
}
