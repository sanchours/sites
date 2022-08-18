<?php

namespace skewer\build\Tool\TasksManager\view;

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
            ->field('id', \Yii::t('TasksManager', 'id'), 'int')
            ->field('global_id', \Yii::t('TasksManager', 'global_id'), 'int')
            ->field('title', \Yii::t('TasksManager', 'title'), 'string', ['listColumns' => ['flex' => 3]])
            /*            ->field( 'class', \Yii::t('TasksManager', 'class'), 's', 'string')
                        ->field( 'parameters', \Yii::t('TasksManager', 'parameters'), 's', 'string')*/
            ->field('priority', \Yii::t('TasksManager', 'priority'), 'string')
            ->field('resource_use', \Yii::t('TasksManager', 'resource_use'), 'string')
            ->field('upd_time', \Yii::t('TasksManager', 'upd_time'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('status', \Yii::t('TasksManager', 'status'), 'string')
            ->buttonConfirm('clear', \Yii::t('adm', 'clear'), \Yii::t('TasksManager', 'clear_confirm'), 'icon-stop')
            ->setValue($this->aItems);
    }
}
