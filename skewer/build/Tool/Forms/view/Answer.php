<?php

namespace skewer\build\Tool\Forms\view;

use skewer\components\ext\view\FormView;

class Answer extends FormView
{
    public $linkAutoReply;
    public $answer;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->linkAutoReply) {
            $this->_form
                ->fieldWithValue(
                    \Yii::t('forms', 'label_link_auto_reply'),
                    '',
                    'show',
                    $this->linkAutoReply
                );
        }

        $this->_form
            ->fieldCheck('answer', \Yii::t('forms', 'form_answer'))
            ->fieldString('title', \Yii::t('forms', 'answer_title'))
            ->fieldWysiwyg('letter', \Yii::t('forms', 'answer_body'), 300)
            ->fieldWithValue(
                'info',
                \Yii::t('forms', 'replace_label'),
                'show',
                \Yii::t('forms', 'head_mail_text', [
                    \Yii::t('app', 'site_label'),
                    \Yii::t('app', 'url_label'),
                ])
            )
            ->setValue($this->answer)
            ->buttonSave('answerSave')
            ->buttonBack('Fields');
    }
}
