<?php

namespace skewer\build\Page\CatalogMaps\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "maps".
 *
 * @property int $id
 * @property string $center
 * @property string $zoom
 */
class Maps extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'maps';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['center', 'zoom'], 'required'],
            [['center'], 'string', 'max' => 255],
            [['zoom'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('Maps', 'ID'),
            'center' => Yii::t('Maps', 'center'),
            'zoom' => Yii::t('Maps', 'zoom'),
        ];
    }

    public static function getSettingsMapById($iMapId)
    {
        if (!($oMap = Maps::findOne($iMapId))) {
            return [];
        }

        $aCenter = StringHelper::explode($oMap->center, ',', true, true);

        return [
            'center' => [
                'lat' => $aCenter[0],
                'lng' => $aCenter[1],
            ],
            'zoom' => $oMap->zoom,
        ];

        return $aLocalSettingsMaps;
    }

    public static function getNewOrExist($iRecordId)
    {
        if ($oRecord = self::findOne($iRecordId)) {
            return $oRecord;
        }

        return new self();
    }
}
