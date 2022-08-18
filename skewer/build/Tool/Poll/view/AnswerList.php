<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 10:06.
 */

namespace skewer\build\Tool\Poll\view;

use skewer\components\ext\view\ListView;

class AnswerList extends ListView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('title', \Yii::t('Poll', 'answer_title'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('value', \Yii::t('Poll', 'answer_value'), 'string')
            ->buttonRowUpdate('showAnswerForm')
            ->buttonRowDelete('answerDelete')
            ->buttonAddNew('showAnswerForm')
            ->buttonCancel('show', \Yii::t('poll', 'back'))
            ->enableDragAndDrop('sortAnswers')
            ->setValue($this->aItems);
    }
}
