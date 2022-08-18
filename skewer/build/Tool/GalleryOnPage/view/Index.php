<?php

namespace skewer\build\Tool\GalleryOnPage\view;

use skewer\components\catalog\model\EntityRow;
use skewer\components\ext\view\ListView;

class Index extends ListView
{
    /** @var EntityRow[] */
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        // Чистка заголовка панели после предыдущих состояний
        $this->_module->setPanelName('');

        $this->_list
            ->fieldString('name', \Yii::t('GalleryOnPage', 'title'), ['listColumns.flex' => 2])
           // ->fieldString( 'parent_class', 'Родитель', ['listColumns.flex' => 2] )
            ->setValue($this->aItems)
            ->buttonRowUpdate('View')
            ->buttonRow('Inherit', \Yii::t('GalleryOnPage', 'inherit_params'), 'icon-clone');
    }
}
