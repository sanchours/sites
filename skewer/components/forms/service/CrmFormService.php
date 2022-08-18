<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use CanapeCrmApi\models\NewDeal;
use skewer\base\log\Logger;
use skewer\base\SysVar;
use skewer\build\Tool\Crm\Api;
use skewer\components\crm\Crm;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\entities\CrmLinkFormEntity;
use skewer\components\forms\forms\CrmLinkFieldForm;
use skewer\components\forms\forms\FormAggregate;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class CrmFormService
{
    public $messageWarning = false;

    public function isInstall(): bool
    {
        return Api::isCrmInstall();
    }

    /**
     * @param FormAggregate $formAggregator
     *
     * @return \Generator
     */
    public function getLinkForField(FormAggregate $formAggregator)
    {
        if (!$this->hasCreatedFields($formAggregator->idForm)) {
            CrmLinkFormEntity::createDefaultFieldsByForm($formAggregator->idForm);
        }

        $fields = CrmLinkFormEntity::getFieldsByIdForm($formAggregator->idForm);

        $fieldsTitle = ArrayHelper::map($formAggregator->fields, 'id', 'title');
        $fieldsRequired = ArrayHelper::map(
            $formAggregator->fields,
            'id',
            'required'
        );

        $links = [];
        $requireMessage = true;

        /** @var CrmLinkFormEntity $field */
        foreach ($fields as $field) {
            $link = new CrmLinkFieldForm($field->id, $field->crm_field_alias);
            if ($field->field_id) {
                $link->fieldTitle = $formAggregator->settings->system
                    ? \Yii::tSingleString($fieldsTitle[$field->field_id])
                    : $fieldsTitle[$field->field_id];
                $link->required = $fieldsRequired[$field->field_id];
                $link->fieldId = $field->field_id;

                if ($link->required && $link->mustMarkAsRequired()) {
                    $requireMessage = false;
                }
            }

            $links[] = $link;
        }

        if ($requireMessage) {
            /** @var CrmLinkFieldForm $link */
            foreach ($links as $link) {
                if ($link->mustMarkAsRequired()) {
                    $link->mark = true;
                }
            }

            $this->messageWarning = true;
        }

        //это нужно чтобы в ExtJs съел данные(сейчас он может обрабатывать только AR, orm/AR
        foreach ($links as &$link) {
            yield $link->getBasicProperties();
        }
    }

    /**
     * @param array $data
     *
     * @throws Exception
     *
     * @return CrmLinkFieldForm
     */
    public function saveLink(array $data): CrmLinkFieldForm
    {
        $linkForm = new CrmLinkFieldForm((int) $data['id']);
        $linkForm->setAttributes($data);

        if ($linkForm->validate()) {
            $link = CrmLinkFormEntity::getFieldsById($linkForm->id);

            $link->field_id = $linkForm->fieldId;
            $link->save();

            return $linkForm;
        }

        throw new Exception(current($linkForm->getErrors()));
    }

    private function hasCreatedFields(int $idForm): bool
    {
        return CrmLinkFormEntity::hasCreatedFields($idForm);
    }

    /**
     * Отправляет данные в CRM.
     *
     * @param BuilderEntity $entity
     * @param null|int $orderId
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function send2Crm(BuilderEntity $entity, int $orderId = null)
    {
        $sType = SysVar::get(
            Api::CRM_SYSVAR_INTEGRATION,
            Api::CRM_EMAIL_INTEGRATION
        );
        if ($sType == Api::CRM_EMAIL_INTEGRATION) {
            $this->sendByEmail($entity, $orderId);
        } else {
            $this->sendByAPI($entity, $orderId);
        }
    }

    /**
     * @param BuilderEntity $entity
     * @param null|int $orderId
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    private function sendByAPI(BuilderEntity $entity, int $orderId = null)
    {
        $crmSender = Api::getCrmCLientInstance();

        $crmDeal = Api::getDealInstance(new NewDeal(), $entity, $orderId);

        try {
            if ($crmDeal->validate()) {
                $crmSender->createDeal($crmDeal);
            }
        } catch (\Exception $e) {
            Logger::dumpException($e);
        }
    }

    /**
     * @param BuilderEntity $entity
     * @param null $orderId
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    private function sendByEmail(BuilderEntity $entity, $orderId = null)
    {
        $sCrm_token = SysVar::get(Api::CRM_SYSVAR_TOKEN_EMAIL);
        $sCrm_email = SysVar::get(Api::CRM_SYSVAR_EMAIL);
        if (!$sCrm_token or !$sCrm_email) {
            return;
        }

        $crmSender = new Crm();
        $crmSender->setToken($sCrm_token);
        $crmSender->setEmail($sCrm_email);
        $crmSender->setDomain(\Yii::$app->request->getServerName());

        $crmSender = Api::getDealInstance($crmSender, $entity, $orderId);
        $crmSender->setDealContent(strip_tags($crmSender->getDealContent()));

        $crmSender->setItemArticle($entity->getInnerParamByName('item_index'));
        $crmSender->setItemTitle($entity->getInnerParamByName('item_title'));
        $crmSender->setItemCount($entity->getInnerParamByName('item_count'));
        $crmSender->setItemPrice($entity->getInnerParamByName('item_price'));
        $crmSender->setItemUnits($entity->getInnerParamByName('item_units'));

        try {
            $crmSender->sendMail();
        } catch (\Exception $e) {
            Logger::dumpException($e);
        }
    }
}
