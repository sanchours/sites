<?php

namespace skewer\build\Tool\Crm;

use CanapeCrmApi\ClientLib;
use CanapeCrmApi\models\Catalog;
use CanapeCrmApi\models\NewDeal;
use skewer\base\orm\Query;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\base\site_module\Request;
use skewer\base\SysVar;
use skewer\build\Adm\Order\ar\Goods;
#65470use skewer\build\Adm\Order\ar\TypeDelivery;
use skewer\build\Tool\Crm\models\DealEvent;
use skewer\build\Tool\Crm\models\DealType;
use skewer\components\catalog\GoodsSelector;
use skewer\components\crm\Crm;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * API работы с резевным копированием
 */
class Api
{
    const CRM_SYSVAR_TOKEN = 'crm_token';
    const CRM_SYSVAR_DOMAIN = 'crm_domain';

    const CRM_SYSVAR_TOKEN_EMAIL = 'crm_token_email';
    const CRM_SYSVAR_EMAIL = 'crm_email';

    const CRM_SYSVAR_INTEGRATION = 'crm_integration';

    const CRM_EMAIL_INTEGRATION = 'email_integration';
    const CRM_API_INTEGRATION = 'api_integration';

    public static function isCrmInstall()
    {
        $oInstaller = new \skewer\components\config\installer\Api();

        return $oInstaller->isInstalled('Crm', Layer::TOOL);
    }

    public static function getCRMIntegrationsList()
    {
        return [
            self::CRM_EMAIL_INTEGRATION => \Yii::t(
                'crm',
                self::CRM_EMAIL_INTEGRATION
            ),
            self::CRM_API_INTEGRATION => \Yii::t(
                'crm',
                self::CRM_API_INTEGRATION
            ),
        ];
    }

    /**
     * @param FormAggregate $formAggregate
     *
     * @return bool
     */
    public static function needSend2CRM(FormAggregate $formAggregate)
    {
        return self::isCrmInstall() && $formAggregate->settings->crm;
    }

    public static function getCRMFieldsList()
    {
        return [
            'deal_title' => 'Заголовок сделки',
            'deal_content' => 'Содержание сделки',
            'contact_client' => 'Имя клиента',
            'contact_email' => 'E-mail клиента',
            'contact_phone' => 'Телефон клиента',
            'contact_mobile' => 'Телефон клиента мобильный',
            'deal_event' => 'Событие',
            'deal_type' => 'Тип сделки',
        ];
    }

    /**
     * Возращает массив полей, где хотя бы одно поле из них должно быть
     * помечено как обязательное.
     */
    public static function getCRMFieldsClientRequiredOne()
    {
        return [
            'contact_client',
            'contact_email',
            'contact_phone',
            'contact_mobile',
        ];
    }

    public static function getDealTypeField4Form($idForm)
    {
        $Res = Query::SelectFrom('crm_link_form')
            ->where('crm_field_alias', 'deal_type')
            ->where('form_id', $idForm)
            ->asArray()
            ->getOne();

        return $Res ? $Res['field_id'] : null;
    }

    public static function getDealEventField4Form($idForm)
    {
        $Res = Query::SelectFrom('crm_link_form')
            ->where('crm_field_alias', 'deal_event')
            ->where('form_id', $idForm)
            ->asArray()
            ->getOne();

        return $Res ? $Res['field_id'] : null;
    }

    public static function getDealTypeList4Form()
    {
        $aDealTypes = DealType::getDealTypesList(true);

        /*
         * @var DealType
         */
        foreach ($aDealTypes as $key => $Type) {
            $aDealTypes[$key] = $Type->id . ':' . $Type->name;
        }

        $sRes = implode(';', $aDealTypes);

        return $sRes;
    }

    public static function getDealEventList4Form()
    {
        $aDealEvents = DealEvent::getDealEventsList();

        /*
         * @var DealEvent
         */
        foreach ($aDealEvents as $key => $Event) {
            $aDealEvents[$key] = $Event->id . ':' . $Event->title;
        }

        $sRes = implode(';', $aDealEvents);

        return $sRes;
    }

    /**
     * @return ClientLib
     */
    public static function getCrmCLientInstance()
    {
        $sDomain = SysVar::get(self::CRM_SYSVAR_DOMAIN);
        $sToken = SysVar::get(self::CRM_SYSVAR_TOKEN);

        $oCrmClient = new ClientLib($sDomain, $sToken);

        $oCrmClient->disallowSSL();

        return $oCrmClient;
    }

    /**
     * @param Crm|NewDeal $oCrmDeal
     * @param BuilderEntity $entity
     * @param null|int $orderId
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return Crm|NewDeal
     */
    public static function getDealInstance(
        $oCrmDeal,
        BuilderEntity $entity,
        int $orderId = null
    ) {
        $oCrmDeal->setDomain(Site::domain());

        $aCrmFields = ArrayHelper::map(
            Query::SelectFrom('crm_link_form')
                ->where(['form_id' => $entity->formAggregate->idForm])
                ->getAll(),
            'crm_field_alias',
            'field_id'
        );

        $sGoodsList = '';

        if ($orderId !== null) {
            $aGoodsList = Goods::find()->where(
                'id_order',
                $orderId
            )->asArray()->getAll();

            foreach ($aGoodsList as $aItem) {
                $sGoodsList .= sprintf(
                    "название: %s, кол-во: %s, цена: %s\r\n",
                    $aItem['title'],
                    $aItem['count'],
                    $aItem['total']
                );
                $aGood = GoodsSelector::get($aItem['id_goods'], 1);
                $oCrmDeal->addCatalogItem(
                    (new Catalog())
                        ->setIndex($aGood['fields']['article']['value'] ?? '')
                        ->setTitle($aItem['title'])
                        ->setCount($aItem['count'])
                        ->setPrice($aItem['price'])
                        ->setUnits($aGood['fields']['measure']['value'] ?? '')
                );
            }
        }

        $aFieldsNames = ArrayHelper::map(
            $entity->getFieldsForCreatedForm(),
            'id',
            'slug'
        );

        $titleForm = $entity->formAggregate->settings->title;

        $sDealContent = Html::tag(
            'strong',
            'Сделка с формы:'
        ) . " '{$titleForm}'\r\n";
        foreach ($aCrmFields as $key => $idField) {
            $sData = '';

            if (isset($aFieldsNames[$idField])) {
                $sData = $entity->getInnerParamByName(
                    $aFieldsNames[$idField],
                    '-'
                );
            }
            switch ($key) {
                case 'deal_title':
                    $oCrmDeal->setDealTitle(
                        $sData ?: "Сделка с формы '{$titleForm}'"
                    );
                    break;
                case 'deal_content':
                    if ($sData && ($sData != '-')) {
                        $sDealContent .= ("\r\n" . $sData . "\r\n");
                    }
                    break;
                case 'contact_client':
                    $oCrmDeal->setContactClient($sData);
                    break;
                case 'contact_email':
                    $oCrmDeal->setContactEmail($sData);
                    break;
                case 'contact_phone':
                    $oCrmDeal->setContactPhone($sData);
                    break;
                case 'contact_mobile':
                    $oCrmDeal->setContactMobile($sData);
                    break;
                case 'deal_event':
                    $oCrmDeal->setEventId($sData);
                    break;
                case 'deal_type':
                    if (method_exists($oCrmDeal, 'setDealTypeId')) {
                        $oCrmDeal->setDealTypeId($sData);
                    }
                    break;
            }
        }

        $oCrmDeal->setCanapeUuid(Request::getStr('_canapeuuid'));

        $sDealContent .= self::getDealContent($entity);

        if ($sGoodsList) {
            $sDealContent .= "\r\n" . Html::tag(
                'b',
                'Товары:'
            ) . "\r\n" . $sGoodsList;
        }

        $oCrmDeal->setDealContent($sDealContent);

        return $oCrmDeal;
    }

    /**
     * @param BuilderEntity $entity
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return string
     */
    private static function getDealContent(BuilderEntity $entity)
    {
        $aDealContent[] = Html::tag('b', 'Поля с формы:');

        /**@param FieldAggregate $field */
        foreach ($entity->getFieldsForCreatedForm() as $field) {
            switch ($field->settings->slug) {
                case 'tp_pay':
                    $sVal = \skewer\build\Tool\DeliveryPayment\Api::getTitleTypePayment((int) $field->param_value);
                    break;
                case 'tp_deliv':
                    $sVal = \skewer\build\Tool\DeliveryPayment\Api::getTitleTypeDelivery((int) $field->param_value);
                    break;
                default:
                    $sVal = $field->field_object->getTrueValue(
                        $field->value,
                        $field->type->default
                    );
            }

            $aDealContent[] = $field->type->getFieldObject()->getParseData4CRM(
                $field,
                $sVal
            );
        }

        return implode("\r\n", $aDealContent);
    }
}
