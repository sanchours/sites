<?php

namespace skewer\build\Adm\Forms;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\build\Adm;
use skewer\build\Adm\Forms\view\Form;
use skewer\build\Design\Zones;
use skewer\components\auth\CurrentAdmin;
use skewer\components\forms;
use skewer\components\forms\service\FormSectionService;
use skewer\components\forms\service\FormService;

/**
 * Модуль добавления формы для раздела.
 */
class Module extends Adm\Tree\ModulePrototype
{
    /** Имя модуля форм */
    protected $sFormModule = '';

    /** @var FormSectionService $_serviceSectionForm */
    private $_serviceSectionForm;
    /** @var FormService $_serviceForm */
    private $_serviceForm;

    protected function preExecute()
    {
        $this->_serviceForm = new FormService();
        $this->_serviceSectionForm = new FormSectionService($this->sectionId());

        parent::preExecute();
    }

    /** {@inheritdoc} */
    protected function actionInit()
    {
        $this->sFormModule = $this->getModuleName();

        $this->setPanelName(\Yii::t('forms', 'select_form'));
        $this->actionPreList();
    }

    /**
     * Состояния: Настройка форм разделов.
     *
     * @throws \Exception
     */
    protected function actionPreList()
    {
        /** Список всех форм */
        $formsForSelection = $this->_serviceSectionForm->getFormsForSelection();

        $this->_serviceSectionForm->get4Section(
            $this->sFormModule,
            $forms,
            true
        );

        // Добавить для каждой формы раздела настройку
        $iKey = 0;
        $formTitles = [];

        $aShowForms = [];
        foreach ($forms as $sGroup => $iFormId) {
            ++$iKey;
            $sGroupTitle = Parameters::getValByName(
                $this->sectionId(),
                $sGroup,
                Zones\Api::layoutTitleName,
                true
            );
            $sFormTitle = $sGroupTitle
                ? "\"{$sGroupTitle}\""
                : ((count($forms) > 1) ? '№' . ($iKey) : '');

            // Добавить имя группы для каждой формы пользователя sys
            if (CurrentAdmin::isSystemMode() and (count($forms) > 1)) {
                $sFormTitle .= " [{$sGroup}]";
            }

            $linkFormEdit = Site::admUrl('Forms', 'tools', $iFormId);
            $aShowForms[$sGroup]['link'] = $this->_serviceForm->hasFormById($iFormId)
                ? \Yii::t('forms', 'link_form', [$linkFormEdit])
                : '';

            $aShowForms[$sGroup]['id'] = $aShowForms[$sGroup]['link'] ? $iFormId : 0;

            $formTitles[$iKey - 1] = $sFormTitle;
        }

        $this->render(new Form([
            'formsForSelection' => $formsForSelection,
            'formInfo' => $aShowForms,
            'formTitles' => $formTitles,
        ]));
    }

    /** Действие: привязка форм к разделу */
    protected function actionLinkFormToSection()
    {
        $aData = $this->get('data');

        foreach ($aData as $sFormLabel => $iFormId) {
            if (mb_strpos($sFormLabel, 'form_') === 0) {
                forms\Api::link2Section(
                    $iFormId,
                    $this->sectionId(),
                    mb_substr($sFormLabel, 5)
                );
            }
        }

        $oTree = Tree::getSection($this->sectionId());
        $oTree->last_modified_date = date('Y-m-d H:i:s', time());
        $oTree->save();

        $this->fireJSEvent('reload_section');
        $this->addModuleNoticeReport(
            \Yii::t('forms', 'editingFormInSection'),
            \Yii::t('forms', 'section_id') . ": {$this->sectionId}()"
        );
        $this->actionPreList();
    }
}
