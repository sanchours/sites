<?php

namespace skewer\build\Catalog\Filters\model;

use skewer\base\ft\Cache;
use skewer\components\ActiveRecord\ActiveRecord;
use Yii;

/**
 * This is the model class for table "FilterSettings4Card".
 *
 * @property int $id
 * @property string $title
 * @property int $card_id
 * @property string $alt_title
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string $staticContent1
 */
class FilterSettings4Card extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'filterSettings4Card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_id'], 'required'],
            [['card_id'], 'integer'],
            [['staticContent1', 'meta_description'], 'string'],
            [['title', 'alt_title', 'meta_title', 'meta_keywords'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'card_id' => 'Card ID',
            'alt_title' => 'Alt Title',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
            'staticContent1' => 'Static Content1',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->title = Yii::t('filters', 'name_setting_for_card');
        }

        return parent::beforeSave($insert);
    }

    public static function getNewOrExist($aCondition)
    {
        return ($oRow = FilterSettings4Card::findOne($aCondition)) ? $oRow : new self();
    }

    /** @return  self */
    public static function findOneByCard($mCard)
    {
        $oCard = Cache::get($mCard);

        return self::findOne(['card_id' => $oCard->getEntityId()]);
    }
}
