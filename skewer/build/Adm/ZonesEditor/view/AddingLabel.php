<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.12.2016
 * Time: 18:25.
 */

namespace skewer\build\Adm\ZonesEditor\view;

use skewer\components\ext\view\ListView;

class AddingLabel extends ListView
{
    public $aCurrentGroupsNames;
    public $aData;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('ZonesEditor', 'module_title'), ['listColumns' => ['flex' => 1]])
            ->setHighlighting('own', \Yii::t('ZonesEditor', 'hint_no_using'), '', 'color: #999999')
            ->setHighlighting('name', '', $this->aCurrentGroupsNames, 'font-weight: bold')
            ->buttonRowAddNew('AddLabel', 'edit_form')
            ->buttonAddNew('editModule')
            ->buttonCancel('EditLayers')
            ->setValue($this->aData);
    }
}
