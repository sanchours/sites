<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 26.01.2017
 * Time: 17:28.
 */

namespace skewer\build\Tool\Schedule\view;

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
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])

            ->field('title', \Yii::t('schedule', 'title'), 'string', ['listColumns' => ['flex' => 3]])

            ->field('c_min', \Yii::t('schedule', 'c_min'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('c_hour', \Yii::t('schedule', 'c_hour'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('c_day', \Yii::t('schedule', 'c_day'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('c_month', \Yii::t('schedule', 'c_month'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('c_dow', \Yii::t('schedule', 'c_dow'), 'string', ['listColumns' => ['flex' => 2]])

            ->field('priority', \Yii::t('schedule', 'priority'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('resource_use', \Yii::t('schedule', 'resource_use'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('target_area', \Yii::t('schedule', 'target_area'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('status', \Yii::t('schedule', 'status'), 'string', ['listColumns' => ['flex' => 2]])

            ->buttonRowUpdate()
            ->buttonAddNew('show')

            ->setValue($this->aItems);
    }
}
