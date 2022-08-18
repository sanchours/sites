<?php

namespace skewer\components\gallery\models;

use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\gallery\Config;
use Yii;

/**
 * This is the model class for table "photogallery_formats".
 *
 * @property int $id
 * @property int $profile_id
 * @property string $title
 * @property string $name
 * @property int $width
 * @property int $height
 * @property int $resize_on_larger_side
 * @property int $scale_and_crop
 * @property int $use_watermark
 * @property string $watermark
 * @property int $watermark_align
 * @property int $active
 * @property int $priority
 */
class Formats extends ActiveRecord
{
    public static function getSizesModes()
    {
        return [
            1 => Yii::t('gallery', 'need_values'),
            2 => Yii::t('gallery', 'max_values'),
        ];
    }

    public function __construct()
    {
        $this->title = \Yii::t('gallery', 'format_title_default');
        $this->active = 1;
        $this->resize_on_larger_side = 1;
        $this->scale_and_crop = 1;
        $this->watermark_align = Config::alignWatermarkBottomRight;
        $this->use_watermark = 0;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photogallery_formats';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['profile_id', 'priority'], 'required', 'message' => \Yii::t('gallery', 'general_field_empty')], // Служебные поля

            [['title'], 'required', 'message' => \Yii::t('gallery', 'field_empty') . ' «' . \Yii::t('gallery', 'formats_title') . '»!'],
            [['name'],  'required', 'message' => \Yii::t('gallery', 'field_empty') . ' «' . \Yii::t('gallery', 'formats_name') . '»!'],
            [['width'], 'required', 'message' => \Yii::t('gallery', 'field_empty') . ' «' . \Yii::t('gallery', 'formats_width') . '»!'],
            [['height'], 'required', 'message' => \Yii::t('gallery', 'field_empty') . ' «' . \Yii::t('gallery', 'formats_height') . '»!'],

            [['profile_id', 'width', 'height', 'resize_on_larger_side', 'scale_and_crop', 'use_watermark', 'watermark_align', 'active', 'priority'], 'integer'],
            [['title', 'name'], 'string', 'max' => 100],
            [['watermark'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Format ID',
            'profile_id' => 'Profile ID',
            'title' => 'Title',
            'name' => 'Name',
            'width' => 'Width',
            'height' => 'Height',
            'resize_on_larger_side' => 'Resize On Larger Side',
            'scale_and_crop' => 'Scale And Crop',
            'use_watermark' => 'Use Watermark',
            'watermark' => 'Watermark',
            'watermark_align' => 'Watermark Align',
            'active' => 'Active',
            'priority' => 'Priority',
            'sizes_mode' => 'Sizes mode',
        ];
    }

    public function beforeSave($insert)
    {
        if (!$this->use_watermark) {
            $this->use_watermark = 0;
        } // Защита от NULL-значения

        return parent::beforeSave($insert);
    }
}
