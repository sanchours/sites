<?php

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;
use skewer\components\forms\forms\TypeResultPageForm;

class SettingsResultPage extends FormView
{
    public $resultPage;

    public function build()
    {
        $this->_form
            ->fieldSelect(
                'type',
                \Yii::t('forms', 'type_result_page'),
                TypeResultPageForm::getTypesResultPage(),
                ['onUpdateAction' => 'updateSettingResultPageForm'],
                false
            );

        if (((int)$this->resultPage['type']) === TypeResultPageForm::RESULT_PAGE_EXTERNAL) {
            $this->_form->fieldString(
                'link',
                \Yii::t('forms', 'form_redirect'),
                ['subtext' => \Yii::t('forms', 'form_redirect_default')]
            );
        }

        if (((int)$this->resultPage['type'])!== TypeResultPageForm::RESULT_PAGE_EXTERNAL) {
            $this->_form->fieldWysiwyg(
                'text',
                \Yii::t('forms', 'form_succ_answer'),
                300
            );
        }

        $this->_form
            ->setValue($this->resultPage)
            ->buttonSave('saveSettingResultPage')
            ->buttonBack('Fields');
    }
}
