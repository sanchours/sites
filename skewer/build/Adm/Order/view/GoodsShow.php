<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 24.11.2016
 * Time: 10:56.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\ListView;

class GoodsShow extends ListView
{
    public $aItems;
    public $iItemId;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])
            ->field('title', \Yii::t('order', 'field_goods_title'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('count', \Yii::t('order', 'field_goods_count'), 'int', ['listColumns' => ['flex' => 3, 'editor' => ['minValue' => 1]]])
            ->field('price', \Yii::t('order', 'field_goods_price'), 'money', ['listColumns' => ['flex' => 3, 'editor' => ['minValue' => 0]]])
            ->field('total', \Yii::t('order', 'field_goods_total'), 'money', ['listColumns' => ['flex' => 3]])
            ->setValue($this->aItems)
            ->buttonRowDelete('deleteDetailGoods')
            ->button('show', \Yii::t('adm', 'back'), 'icon-cancel', 'show', ['addParams' => ['data' => ['id' => $this->iItemId]]])
            ->buttonAddNew('showAddGoodsList', \Yii::t('order', 'field_add_goods'))
            ->setEditableFields(['price', 'count'], 'editDetailGoods');
    }
}
