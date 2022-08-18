<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 12.01.2017
 * Time: 15:04.
 */

namespace skewer\build\Design\CSSEditor\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aCssFiles;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])
            ->field('name', \Yii::t('design', 'field_name'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('last_upd', \Yii::t('design', 'field_last_upd'), 'string')
            ->field('active', \Yii::t('design', 'field_active'), 'check')

            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->buttonRow('export', \Yii::t('design', 'export_button'), 'icon-clone')
            ->buttonAddNew('show')
            ->setValue($this->aCssFiles)
            ->setEditableFields(['active'], 'saveFromList')
            ->enableDragAndDrop('sort');
    }
}
