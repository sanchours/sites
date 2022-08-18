<?php

namespace skewer\components\gallery\models;

use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\gallery\Format;
use skewer\components\gallery\Profile;

/**
 * This is the model class for table "photogallery_profiles".
 *
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string $type
 * @property int $active
 * @property int $default
 * @property string $watermark_color
 */
class Profiles extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        $this->type = Profile::TYPE_SECTION;
        $this->title = \Yii::t('gallery', 'profile_title_default');
        $this->active = 1;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photogallery_profiles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => \Yii::t('gallery', 'field_empty') . ' «' . \Yii::t('gallery', 'profiles_title') . '»!'],
            [['watermark_color'], 'string'],
            [['alias'], 'required', 'message' => \Yii::t('gallery', 'field_empty') . ' «' . \Yii::t('gallery', 'profiles_alias') . '»!'],
            [['type'],  'required', 'message' => \Yii::t('gallery', 'general_field_empty')],

            [['active'], 'integer'],
            // [['default'], 'unsafe'], Поле default не должно здесь быть. Для установки профиля по умолчанию используется только метод \skewer\components\gallery\Profile::setDefaultProfile()
            [['title', 'type'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Profile ID',
            'title' => 'Title',
            'alias' => 'Alias',
            'active' => 'Active',
            'type' => 'Type',
            'default' => 'Default',
            'watermark_color' => 'Watermark color',
        ];
    }

    /** {@inheritdoc} */
    public function afterSave($insert, $changedAttributes)
    {
        // Создание базовых форматов для каждого типа профиля при создании профиля
        if ($insert) {
            switch ($this->type) {
            case Profile::TYPE_CATALOG:
                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_catalog_mini_name', [], \Yii::$app->language),
                    'name' => 'mini',
                    'width' => 80,
                    'height' => 80,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_catalog_small_name', [], \Yii::$app->language),
                    'name' => 'small',
                    'width' => 180,
                    'height' => 180,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 2,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_catalog_medium_name', [], \Yii::$app->language),
                    'name' => 'medium',
                    'width' => 320,
                    'height' => 320,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 3,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_catalog_big_name', [], \Yii::$app->language),
                    'name' => 'big',
                    'width' => 1600,
                    'height' => 0,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 4,
                ]);
                break;

            case Profile::TYPE_CATALOG_ADD:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_catalog_tile_name', [], \Yii::$app->language),
                    'name' => 'tile',
                    'width' => 250,
                    'height' => 250,
                    'resize_on_larger_side' => 1,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_catalog_medium_name', [], \Yii::$app->language),
                    'name' => 'big',
                    'width' => 800,
                    'height' => 0,
                    'resize_on_larger_side' => 1,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                break;

            case Profile::TYPE_CATALOG4COLLECTION:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_collection_min_name', [], \Yii::$app->language),
                    'name' => 'colmin',
                    'width' => 160,
                    'height' => 98,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_collection_med_name', [], \Yii::$app->language),
                    'name' => 'colmed',
                    'width' => 200,
                    'height' => 200,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_collection_max_name', [], \Yii::$app->language),
                    'name' => 'colmax',
                    'width' => 800,
                    'height' => 0,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);
                break;

            case Profile::TYPE_NEWS:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_news_mini_name', [], \Yii::$app->language),
                    'name' => 'mini',
                    'width' => 70,
                    'height' => 47,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_news_list_name', [], \Yii::$app->language),
                    'name' => 'list',
                    'width' => 160,
                    'height' => 98,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 2,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_news_on_main_name', [], \Yii::$app->language),
                    'name' => 'on_main',
                    'width' => 300,
                    'height' => 170,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 3,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_news_column', [], \Yii::$app->language),
                    'name' => 'column',
                    'width' => 225,
                    'height' => 170,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 4,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_news_big_name', [], \Yii::$app->language),
                    'name' => 'big',
                    'width' => 862,
                    'height' => 575,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 5,
                ]);

                break;

            case Profile::TYPE_OPENGRAPH:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_openGraph', [], \Yii::$app->language),
                    'name' => 'format_openGraph',
                    'width' => 980,
                    'height' => 510,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                break;
            case Profile::TYPE_REVIEWS:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_reviews_main', [], \Yii::$app->language),
                    'name' => 'main',
                    'width' => 64,
                    'height' => 64,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_reviews_detail', [], \Yii::$app->language),
                    'name' => 'detail',
                    'width' => 220,
                    'height' => 220,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 2,
                ]);

                break;
            case Profile::TYPE_CATEGORYVIEWER:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_section_preview_name', [], \Yii::$app->language),
                    'name' => 'preview',
                    'width' => 220,
                    'height' => 220,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);

                break;

            case Profile::TYPE_ARTICLES:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_articles_small', [], \Yii::$app->language),
                    'name' => 'mini',
                    'width' => 370,
                    'height' => 270,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_articles_big', [], \Yii::$app->language),
                    'name' => 'big',
                    'width' => 770,
                    'height' => 510,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 2,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_articles_medium', [], \Yii::$app->language),
                    'name' => 'medium',
                    'width' => 370,
                    'height' => 270,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                    'position' => 1,
                ]);

                break;

            case Profile::TYPE_DICT:

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_dict_icon', [], \Yii::$app->language),
                    'name' => 'icon',
                    'width' => 16,
                    'height' => 16,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 0,
                    'active' => 1,
                    'position' => 1,
                ]);

                break;

            default:
                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_section_mini_name', [], \Yii::$app->language),
                    'name' => 'mini',
                    'width' => 70,
                    'height' => 47,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);
                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_section_preview_name', [], \Yii::$app->language),
                    'name' => 'preview',
                    'width' => 0,
                    'height' => 160,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 1,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_section_med_name', [], \Yii::$app->language),
                    'name' => 'med',
                    'width' => 1600,
                    'height' => 0,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 1,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_section_preview_ver_name', [], \Yii::$app->language),
                    'name' => 'preview_ver',
                    'width' => 0,
                    'height' => 400,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 0,
                ]);

                Format::setFormat([
                    'profile_id' => $this->id,
                    'title' => \Yii::t('data/gallery', 'format_section_preview_hor_name', [], \Yii::$app->language),
                    'name' => 'preview_hor',
                    'width' => 500,
                    'height' => 0,
                    'resize_on_larger_side' => 0,
                    'scale_and_crop' => 0,
                    'use_watermark' => 0,
                    'watermark' => '',
                    'watermark_align' => 84,
                    'active' => 0,
                ]);

                break;
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }
}
