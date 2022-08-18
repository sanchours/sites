<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 10:56.
 */

namespace skewer\build\Tool\Gallery\view;

use skewer\components\ext\view\ListView;

class FormatsList extends ListView
{
    public $aItems;
    public $bIsSystemMode;
    public $iProfileId;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->fieldString('title', \Yii::t('gallery', 'formats_title'), ['listColumns' => ['flex' => 2]])
            ->fieldString('width', \Yii::t('gallery', 'formats_width'), ['listColumns' => ['flex' => 1]])
            ->fieldString('height', \Yii::t('gallery', 'formats_height'), ['listColumns' => ['flex' => 1]])
            ->fieldString('active', \Yii::t('gallery', 'formats_active'), ['listColumns' => ['flex' => 1]])
            ->setValue($this->aItems)
            ->buttonRowUpdate('addUpdFormat');
        if ($this->bIsSystemMode) {
            $this->_list->buttonRowDelete('delFormat');
        }

        $this->_list->button('addUpdProfile', \Yii::t('gallery', 'tools_backToProfile'), 'icon-cancel', 'init', ['addParams' => ['data' => ['profile_id' => $this->iProfileId]]])
            ->buttonAddNew('addUpdFormat', \Yii::t('adm', 'add'), ['addParams' => ['data' => ['profile_id' => $this->iProfileId]]])
            ->enableDragAndDrop('sortFormats');
    }
}
