<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\Parameters;
use skewer\components\catalog;
use skewer\components\forms\Api as ApiForms;
use skewer\components\forms\entities\FormLinkEntity;
use skewer\components\forms\service\FormSectionService;
use skewer\components\forms\service\FormService;

/**
 * Форма заказа.
 */
class OrderForm extends Prototype
{
    private $iForm = 0;

    private $defaultFields = [
        'naimenovanie-tovara' => 'goods_name',
        'person' => 'person',
        'phone' => 'phone',
        'email' => 'email',
        'text' => 'text',
    ];

    protected $copyList = [];

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();

        $this->listenTo(CopySections::COPY_SECTIONS, 'setCopySections');
    }

    public function setCopySections($aParams)
    {
        $this->copyList = $aParams;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $iSourceSection = \Yii::$app->sections->getValue('orderForm', $this->getSourceLanguageName());

        if (!$iSourceSection) {
            return;
        }

        $formSectionService = new FormSectionService($iSourceSection);

        $forms = $formSectionService->get4Section();

        $sourceForm = reset($forms);

        if (!$sourceForm) {
            return;
        }

        $iSection = \Yii::$app->sections->getValue('orderForm', $this->getLanguageName());

        if (!$iSection) {
            return;
        }

        $serviceForm = new FormService();
        $this->iForm = $serviceForm->cloneForm($sourceForm->idForm);

        $this->copyLinks($sourceForm->idForm);

        // Связь раздела заказа с формой
        ApiForms::link2Section($this->iForm, $iSection);

        $this->setParams($iSection);
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        /*
         * Удалять форму нельзя - мало ли где используется?
         * Раздел удалять не будем - если что-то не так, он удалиться в Action CopySections
         */
    }

    /**
     * Копирование связей с полями.
     *
     * @param int $idSourceForm
     */
    protected function copyLinks(int $idSourceForm)
    {
        $links = FormLinkEntity::getLinksByIdForm($idSourceForm);
        /** @var FormLinkEntity $link */
        foreach ($links as $link) {
            $linkEntity = new FormLinkEntity();
            $linkEntity->form_id = $this->iForm;
            $linkEntity->form_field = $link->form_field;
            $linkEntity->card_field = $link->card_field;
            $linkEntity->save();
        }
    }

    /**
     * Установка раздела с формой заказа для разделов новой языковой ветки.
     *
     * @param int $iSection
     */
    protected function setParams($iSection)
    {
        $aParams = Parameters::getList(array_values($this->copyList))
            ->group('content')
            ->name('buyFormSection')
            ->get();
        if ($aParams) {
            foreach ($aParams as $oParam) {
                $oParam->value = $iSection;
                $oParam->save();
            }
        }

        /** Глобальный языковой параметр */
        $oParamFormSection = Parameters::getByName($this->getRootSection(), catalog\Api::LANG_GROUP_NAME, 'buyFormSection');
        if ($oParamFormSection) {
            $oParamFormSection->value = $iSection;
            $oParamFormSection->save();
        }
    }
}
