<?php

namespace skewer\build\Tool\Domains\models;

use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "domains".
 *
 * @property int $d_id
 * @property int $domain_id
 * @property string $domain
 * @property int $prim
 */
class Domain extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domains';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'domain', 'prim'], 'required'],
            [['domain_id', 'prim'], 'integer'],
            [['domain'], 'string', 'max' => 255],
        ];
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new Domain();

        $oRow->domain = '';
        $oRow->prim = '0';

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'd_id' => 'D ID',
            'domain_id' => \Yii::t('domains', 'module_id'),
            'domain' => \Yii::t('domains', 'module_domain_name'),
            'prim' => \Yii::t('domains', 'module_main_domain'),
        ];
    }
}
