<?php

namespace skewer\build\Tool\Crm\models;

use CanapeCrmApi\ClientLib;
use skewer\base\SysVar;
use skewer\build\Tool\Crm\Api;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class DealType.
 *
 * @property int $id
 * @property string $name
 * @property string $name_site
 * @property string $name_crm
 * @property bool $active
 */
class DealType extends ActiveRecord
{
    public $default;

    public static function tableName()
    {
        return 'crm_deal_types';
    }

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['name', 'name_site', 'name_crm'], 'string', 'max' => 128],
            [['active'], 'boolean'],
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name_site ?: $this->name_crm;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        if ($this->name_crm == $name) {
            $this->name_site = '';
        } else {
            $this->name_site = $name;
        }
    }

    /**
     * @param mixed $bOnlyActive
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDealTypesList($bOnlyActive = false)
    {
        $oQuery = self::find()->indexBy('id');

        if ($bOnlyActive) {
            $oQuery->where(['active' => 1]);
        }

        return $oQuery->all();
    }

    public static function checkList()
    {
        $aCMSDealTypes = self::getDealTypesList();

        $sDomain = SysVar::get(Api::CRM_SYSVAR_DOMAIN);
        $sToken = SysVar::get(Api::CRM_SYSVAR_TOKEN);

        $aCRMDealTypes = [];
        if ($sDomain && $sToken) {
            $oClient = new ClientLib($sDomain, $sToken);
            $aCRMDealTypes = $oClient->disallowSSL()->getDealTypes()->json();
        }
        $aIdCRMDeals = ArrayHelper::map($aCRMDealTypes, 'id', 'id');

        foreach ($aCMSDealTypes as $oDealType) {
            if (!in_array($oDealType->id, $aIdCRMDeals)) {
                $oDealType->delete();
            }
        }

        foreach ($aCRMDealTypes as $aDealType) {
            $aDealType['name_crm'] = $aDealType['name'];
            unset($aDealType['name']);
            if (!isset($aCMSDealTypes[$aDealType['id']])) {
                $oDealType = new DealType($aDealType);
                $oDealType->active = 1;
                $oDealType->save();
            } else { //есть, синхронизируем
                $oCMSDealType = DealType::findOne($aDealType['id']);
                $oCMSDealType->setAttributes($aDealType);
                $oCMSDealType->save();
            }
        }
    }
}
