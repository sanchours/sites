<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.01.2017
 * Time: 13:05.
 */

namespace skewer\build\Tool\Messages\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aMessages;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('messages', 'field_title'), ['listColumns' => ['flex' => 1]])
            ->fieldString('type', \Yii::t('messages', 'field_status'))
            ->fieldString('arrival_date', \Yii::t('messages', 'field_date'), ['listColumns' => ['width' => 150]])
            ->buttonRow('msgShow', \Yii::t('adm', 'view'), 'icon-view', 'edit_form')
            ->buttonRowDelete('msgDelete')
            ->setValue($this->aMessages);
    }
}
