<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 10:21.
 */

namespace skewer\build\Tool\Poll\view;

use skewer\components\ext\view\FormView;

class ShowAnswerForm extends FormView
{
    public $iItemId;
    public $aItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('answer_id', 'id')
            ->fieldString('title', \Yii::t('Poll', 'answer_title'), ['listColumns' => ['flex' => 1]])
            ->fieldInt('value', \Yii::t('Poll', 'answer_value'), ['minValue' => 0])
            ->buttonSave('answerSave')
            ->buttonCancel('answerList');

        if ($this->iItemId) {
            $this->_form->buttonSeparator('->')
                ->buttonDelete('answerDelete');
        }
        $this->_form->setValue($this->aItem);
    }
}
