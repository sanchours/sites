<?php

namespace skewer\build\Design\CSSEditor\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $name
 * @property string $last_upd
 * @property string $data
 * @property string $active
 * @property string $priority
 */
class CssFiles extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'css_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['name', 'last_upd', 'data', 'active', 'priority'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('design', 'field_id'),
            'name' => Yii::t('design', 'field_name'),
            'last_upd' => Yii::t('design', 'field_last_upd'),
            'data' => Yii::t('design', 'field_data'),
            'active' => Yii::t('design', 'field_active'),
        ];
    }

    public static function sortValues(array $aItemDrop, array $aItemTarget, $sOrderType = 'before')
    {
        $sSortField = 'priority';

        // Выбираем направление сдвига
        if ($aItemDrop[$sSortField] < $aItemTarget[$sSortField]) {
            $sSign = '-1';
            $iNewPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] : $aItemTarget[$sSortField] - 1;
            $iStartPos = $aItemDrop[$sSortField];
            $iEndPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] + 1 : $aItemTarget[$sSortField];
        } else {
            $sSign = '+1';
            $iNewPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] + 1 : $aItemTarget[$sSortField];
            $iStartPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] : $aItemTarget[$sSortField] - 1;
            $iEndPos = $aItemDrop[$sSortField];
        }

        Yii::$app->db->createCommand('
            UPDATE `' . self::tableName() . "`
            SET `{$sSortField}` = `{$sSortField}` {$sSign}
            WHERE
                `{$sSortField}` > {$iStartPos} AND
                `{$sSortField}` < {$iEndPos}
        ")->execute();

        \Yii::$app->db->createCommand('
            UPDATE `' . self::tableName() . "`
            SET `{$sSortField}` = {$iNewPos}
            WHERE `id` = " . $aItemDrop['id'] . '
        ')->execute();
    }
}
