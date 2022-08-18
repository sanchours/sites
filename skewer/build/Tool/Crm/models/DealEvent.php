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
 * @property string $title_site
 * @property string $title
 * @property string $title_crm
 * @property string $from
 * @property string $to
 * @property bool $active
 */
class DealEvent extends ActiveRecord
{
    public $default;

    public static function tableName()
    {
        return 'crm_deal_events';
    }

    public function rules()
    {
        return [
            [['id', 'title'], 'required'],
            [['id'], 'integer'],
            [['title_site', 'title_crm', 'title'], 'string', 'max' => 128],
            [['from', 'to'], 'string'],
            [['active'], 'boolean'],
        ];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title_site ?: $this->title_crm;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        if ($this->title_crm == $title) {
            $this->title_site = '';
        } else {
            $this->title_site = $title;
        }
    }

    /**
     * @param bool $bOnlyActive
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDealEventsList($bOnlyActive = false)
    {
        $oQuery = self::find()->indexBy('id');

        if ($bOnlyActive) {
            $oQuery->where(['active' => 1]);
        }

        return $oQuery->all();
    }

    public static function checkList()
    {
        $aCMSDealEvents = self::getDealEventsList();

        $sDomain = SysVar::get(Api::CRM_SYSVAR_DOMAIN);
        $sToken = SysVar::get(Api::CRM_SYSVAR_TOKEN);

        $aCRMDealEvents = [];
        if ($sDomain && $sToken) {
            $oClient = new ClientLib($sDomain, $sToken);
            $aCRMDealEvents = $oClient->disallowSSL()->getEvents()->json();
        }
        $aIdCRMDeals = ArrayHelper::map($aCRMDealEvents, 'id', 'id');

        foreach ($aCMSDealEvents as $oDealType) {
            if (!in_array($oDealType->id, $aIdCRMDeals)) {
                $oDealType->delete();
            }
        }

        foreach ($aCRMDealEvents as $aDealEvent) {
            $aDealEvent['title_crm'] = $aDealEvent['title'];
            unset($aDealEvent['title']);
            if (!isset($aCMSDealEvents[$aDealEvent['id']])) { //нет, создадим
                $oDealEvent = new DealEvent($aDealEvent);
                $oDealEvent->active = 1;
                $oDealEvent->save();
            } else { //есть, синхронизируем
                $oCMSDealEvent = DealEvent::findOne($aDealEvent['id']);
                $oCMSDealEvent->setAttributes($aDealEvent);
                $oCMSDealEvent->save();
            }
        }
    }
}
