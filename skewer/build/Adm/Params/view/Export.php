<?php

namespace skewer\build\Adm\Params\view;

use skewer\components\ext\view\ListView;
use Yii;

class Export extends ListView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->showCheckboxSelection()
            ->enableSorting()
            ->sortBy('updated_at', 'DESC')
            ->fieldString('updated_at', Yii::t('params', 'updated_at'), ['listColumns' => ['width' => 120]])
            ->fieldString('group', Yii::t('params', 'group'), ['listColumns' => ['width' => 150], 'sorted' => true])
            ->fieldString('name', Yii::t('params', 'name'), ['listColumns' => ['width' => 150], 'sorted' => true])
            ->fieldString('title', Yii::t('params', 'title'), ['listColumns' => ['flex' => 3]])
            ->fieldString('value', Yii::t('params', 'value'), ['listColumns' => ['flex' => 5]])
            ->fieldString('id', 'ID', ['listColumns' => ['width' => 40]])
            ->setValue($this->aItems)
            ->buttonSave('export', Yii::t('params', 'export'))

            ->buttonCancel();
    }
}
