<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 10:43.
 */

namespace skewer\build\Tool\ReachGoal\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aTypes;
    public $aTargets;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])
            ->field('title', \Yii::t('ReachGoal', 'field_title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('type', \Yii::t('ReachGoal', 'field_type'), 'string', ['listColumns' => ['flex' => 3]])
            ->buttonRowUpdate()
            ->buttonRowDelete();
        foreach ($this->aTypes as $type) {
            $this->_list->buttonAddNew('showForm', \Yii::t('ReachGoal', 'btn_add_' . mb_strtolower($type)), ['addParams' => ['type' => mb_strtolower($type)]]);
        }
        $this->_list->button('Settings', \Yii::t('ReachGoal', 'btn_settings'))
            ->button('ShowSelectors', \Yii::t('ReachGoal', 'btn_selectors'));
        $this->_list->setValue($this->aTargets);
    }
}
