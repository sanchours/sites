<?php

namespace skewer\components\search\models;

use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\search\Api;

/**
 * This is the model class for table "search_index".
 *
 * @property int $id
 * @property string $search_title
 * @property string $search_text
 * @property int $status
 * @property string $href
 * @property string $class_name
 * @property int $object_id
 * @property string $language
 * @property int $section_id
 * @property int $use_in_search
 * @property float $priority
 * @property string $frequency
 * @property int $use_in_sitemap
 * @property int $has_real_url
 * @property string $modify_date
 * @property string $text
 */
class SearchIndex extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'search_index';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
//            [['search_title'/*, 'search_text', 'status', 'href', 'class_name', 'object_id', 'language', 'section_id', 'use_in_search', 'use_in_sitemap'*/], 'required'],
            [['search_text', 'text'], 'string'],
            [['object_id', 'section_id'], 'integer'],
            [['status', 'use_in_search', 'use_in_sitemap', 'has_real_url'], 'boolean'],
            [['priority'], 'number'],
            [['modify_date'], 'safe'],
            [['search_title', 'href', 'class_name'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 64],
            [['frequency'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'search_title' => \Yii::t('search', 'field_search_title'),
            'search_text' => \Yii::t('search', 'field_search_text'),
            'status' => \Yii::t('search', 'field_status'),
            'href' => \Yii::t('search', 'field_href'),
            'class_name' => \Yii::t('search', 'field_class_name'),
            'object_id' => \Yii::t('search', 'field_object_id'),
            'language' => \Yii::t('search', 'field_language'),
            'section_id' => \Yii::t('search', 'field_section_id'),
            'use_in_search' => \Yii::t('search', 'field_use_in_search'),
            'priority' => \Yii::t('search', 'field_priority'),
            'frequency' => \Yii::t('search', 'field_frequency'),
            'use_in_sitemap' => \Yii::t('search', 'field_use_in_sitemap'),
            'has_real_url' => \Yii::t('search', 'field_has_real_url'),
            'modify_date' => \Yii::t('search', 'field_modify_date'),
            'text' => \Yii::t('search', 'field_text'),
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        // Добавить в поиск заголовок
        $sHandledTitle = Api::indexFirstCharsString($this->search_title);
        $this->search_text = sprintf('%s %s %s', $this->search_title, $sHandledTitle, $this->search_text);

        $this->search_text = Api::prepareSearchText($this->search_text, true);

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $res = parent::delete();
        if ($res) {
            $this->id = 0;
        }

        return $res;
    }
}
