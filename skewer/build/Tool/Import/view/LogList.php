<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 03.03.2017
 * Time: 10:46.
 */

namespace skewer\build\Tool\Import\view;

use skewer\components\ext\view\ListView;

class LogList extends ListView
{
    public $sWidgetClsName;
    public $aLogs;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id_log', 'ID', 'hide')
            ->field('start', \Yii::t('import', 'log_start'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('status', \Yii::t('import', 'log_status'), 'string', ['listColumns' => ['flex' => 1]])
            ->widget('status', $this->sWidgetClsName, 'getStatus')
            ->setValue($this->aLogs)
            ->buttonRow('detailLog', \Yii::t('import', 'log_detail'), 'icon-page', 'edit_form')
            ->buttonRowDelete('deleteLog')
            ->buttonCancel('headSettings', \Yii::t('import', 'back'));
    }
}
