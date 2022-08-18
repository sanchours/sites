<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 18:28.
 */

namespace skewer\build\Tool\Poll\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $aPollLocations;
    public $aSectionTitles;
    public $iItemId;
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', \Yii::t('Poll', 'field_id'))
            ->fieldString('title', \Yii::t('Poll', 'title'))
            ->fieldString('question', \Yii::t('Poll', 'question'))
            ->fieldSelect('location', \Yii::t('Poll', 'location'), $this->aPollLocations, [], false)
            ->fieldSelect('section', \Yii::t('Poll', 'section'), $this->aSectionTitles, [], false)
            ->fieldCheck('active', \Yii::t('Poll', 'active'))
            ->fieldCheck('on_main', \Yii::t('poll', 'onMainTitle'))
            ->fieldCheck('on_allpages', \Yii::t('Poll', 'onAllTitle'))
            ->fieldCheck('on_include', \Yii::t('Poll', 'onInternalTitle'))

            // добавление кнопок в боковую (слева) колонку
            ->buttonSave()
            ->buttonCancel();

        if ($this->iItemId) {
            $this->_form->button('answerList', \Yii::t('poll', 'answersTitle'), 'icon-edit', 'answerList')
                ->buttonSeparator('->')
                ->buttonDelete();
        }
        $this->_form->setValue($this->aData);
    }
}
