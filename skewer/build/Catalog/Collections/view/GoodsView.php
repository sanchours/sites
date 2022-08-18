<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 29.12.2016
 * Time: 14:51.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\base\ft\Editor;
use skewer\components\ext\view\ListView;

class GoodsView extends ListView
{
    public $aItems;
    public $editor;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldShow('id', 'id', 'i', ['listColumns.width' => 40])
            ->fieldString('title', \Yii::t('collections', 'goods_name'), ['listColumns.flex' => 1])
            ->setValue($this->aItems)
            ->buttonBack('ItemEdit', null, ['skipData' => true]);

        if ($this->editor == Editor::MULTICOLLECTION) {
            $this->_list
                ->enableDragAndDrop('dragAndDrop');
        }
    }
}
