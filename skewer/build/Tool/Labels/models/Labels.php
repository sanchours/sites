<?php

namespace skewer\build\Tool\Labels\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "{{%labels}}".
 *
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string $default
 */
class Labels extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%labels}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'alias'], 'required'],
            [['alias'], 'unique'],
            [['title', 'alias'], 'string', 'length' => [2, 250]],
            [['default'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('labels', 'id'),
            'title' => \Yii::t('labels', 'title'),
            'alias' => \Yii::t('labels', 'alias'),
            'default' => \Yii::t('labels', 'default'),
        ];
    }

    public static function getAll()
    {
        return self::find()->all();
    }

    public static function getSearchLabels($search)
    {
        return self::find()->where(
            [
                'or',
                ['like', 'title', $search],
                ['like', 'alias', $search],
                ['like', 'default', $search],
            ]
        )->all();
    }

    public static function getNewRow($params = [])
    {
        $label = new Labels();
        if ($params) {
            $label->setAttributes($params);
        }

        return $label;
    }

    /**
     * @param $id
     *
     * @return Labels
     */
    public static function getById($id)
    {
        $label = Labels::findOne(['id' => $id]);

        return $label ?: self::getNewRow();
    }

    /**
     * @param $id
     *
     * @return null|ActiveRecord|array
     */
    public static function getByIdAsArray($id)
    {
        return Labels::find()
            ->where(['id' => $id])
            ->asArray()
            ->one();
    }
}
