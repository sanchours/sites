<?php

namespace skewer\build\Tool\SEOTemplates\view;

use skewer\components\catalog\Card;
use skewer\components\catalog\Section;
use skewer\components\ext\view\FormView;

class CloneForm extends FormView
{
    /**
     * @var array Значения полей формы
     */
    public $aValues = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $aCardList = Card::getGoodsCards();
        $aData = [];
        foreach ($aCardList as $oCard) {
            $aData[$oCard->name] = $oCard->title;
        }

        $this->_form
            ->fieldSelect('card', \Yii::t('SEO', 'select_card'), $aData, ['onUpdateAction' => 'updateCloneForm', 'disabled' => $this->doDisableFieldForm('card')])
            ->fieldSelect('section', \Yii::t('SEO', 'section'), Section::getList(), ['onUpdateAction' => 'updateCloneForm', 'disabled' => $this->doDisableFieldForm('section')])
            ->setValue($this->aValues)
            ->buttonSave('clone')
            ->buttonCancel('list');
    }

    /**
     * Отключить поле формы?
     *
     * @param $sFieldName - название поля
     *
     * @return bool
     */
    private function doDisableFieldForm($sFieldName)
    {
        if ($this->aValues) {
            $bIsEmptyArray = true;
            foreach ($this->aValues as $aValue) {
                if ($aValue) {
                    $bIsEmptyArray = false;
                    break;
                }
            }

            if (empty($this->aValues[$sFieldName])) {
                return ($bIsEmptyArray) ? false : true;
            }
        } else {
            return false;
        }
    }
}
