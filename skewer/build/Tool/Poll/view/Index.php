<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 18:03.
 */

namespace skewer\build\Tool\Poll\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('title', \Yii::t('Poll', 'title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('question', \Yii::t('Poll', 'question'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('section', \Yii::t('Poll', 'section'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('active', \Yii::t('Poll', 'active'), 'check', ['listColumns' => ['width' => 70]])
            ->field('on_main', \Yii::t('poll', 'onMainTitle'), 'check', ['listColumns' => ['width' => 70]])
            ->field('on_allpages', \Yii::t('Poll', 'onAllTitle'), 'check', ['listColumns' => ['width' => 50]])
            ->field('on_include', \Yii::t('Poll', 'onInternalTitle'), 'check', ['listColumns' => ['width' => 90, 'align' => 'center']])
            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->setGroups('locationTitle')    // разбиение на группы (горизонтальная полоска с названием зоны вывода)
            ->enableDragAndDrop('sortPolls')

            ->buttonAddNew('show')         // Кнопка "Добавить" в боковой колонке
            ->setValue($this->aItems);
    }
}
