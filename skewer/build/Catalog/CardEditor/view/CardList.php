<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 29.12.2016
 * Time: 15:32.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\components\ext\view\ListView;

class CardList extends ListView
{
    public $oGoodsCards;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->fieldShow('id', 'id', 'i', ['listColumns.width' => 40])
            ->fieldString('type', \Yii::t('card', 'field_type'))
            ->fieldString('title', \Yii::t('card', 'field_title'), ['listColumns.flex' => 1])
            ->widget('type', 'skewer\\build\\Catalog\\CardEditor\\Api', 'applyTypeWidget')
            ->setValue($this->oGoodsCards)
            ->buttonAddNew('CardEdit', \Yii::t('card', 'btn_add_card'))
            ->button('GroupList', \Yii::t('card', 'btn_groups'), 'icon-edit')
            ->buttonRowUpdate('FieldList');
    }
}
