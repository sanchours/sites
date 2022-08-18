<?php

namespace skewer\components\gallery\models;

use skewer\build\Adm\Gallery\Search;
use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\gallery\Album;

/**
 * This is the model class for table "photogallery_photos".
 *
 * @property int $id
 * @property int $album_id
 * @property string $title
 * @property string $alt_title
 * @property string $description
 * @property string $creation_date
 * @property int $visible
 * @property int $priority
 * @property string $images_data
 * @property string $thumbnail
 * @property string $source
 *
 * @method static Photos|null findOne($condition)
 */
class Photos extends ActiveRecord
{
    /** @var [] Массив с картинками */
    public $pictures = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'photogallery_photos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['album_id'], 'required', 'message' => \Yii::t('gallery', 'general_field_empty')], // Служебные поля

            [['album_id', 'visible', 'priority'], 'integer'],
            [['creation_date'], 'safe'],
            [['images_data', 'thumbnail', 'source'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['alt_title'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 512],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('gallery', 'images_id'),
            'album_id' => \Yii::t('gallery', 'images_album_id'),
            'title' => \Yii::t('gallery', 'images_title'),
            'alt_title' => \Yii::t('gallery', 'images_alt_title'),
            'description' => \Yii::t('gallery', 'images_description'),
            'creation_date' => \Yii::t('gallery', 'images_creation_date'),
            'visible' => \Yii::t('gallery', 'images_visible'),
            'priority' => \Yii::t('gallery', 'images_priority'),
            'images_data' => \Yii::t('gallery', 'images_images_data'),
            'thumbnail' => \Yii::t('gallery', 'images_thumbnail'),
            'source' => \Yii::t('gallery', 'images_source'),
        ];
    }

    /**
     * Возвращает массив с картинками по форматам
     *
     * @return array
     */
    public function getPictures()
    {
        if (is_array($this->images_data)) {
            return $this->images_data;
        }

        return json_decode($this->images_data, true) ?: [];
    }

    /** Сохранить в JSON перед записью в базу */
    public function beforeSave($insert)
    {
        $oAlbum = Album::getById($this->album_id);
        $oAlbum->last_modified_date = date('Y-m-d H:i:s', time());
        $oAlbum->save();

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $oSearch = new Search();
        $oSearch->updateByObjectId($this->album_id);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $oAlbum = Album::getById($this->album_id);
        $oAlbum->last_modified_date = date('Y-m-d H:i:s', time());
        $oAlbum->save();

        $oSearch = new Search();
        $oSearch->updateByObjectId($this->album_id);
    }
}
