<?php

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\ListView;

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 18:30.
 */
class Index extends ListView
{
    public $aStatusList;
    public $iStatusFilter;
    public $aItems;
    public $mDateFilter1;
    public $mDateFilter2;
    public $sPersonFilter = '';
    public $sIdFilter = '';

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->filterSelect('filter_status', $this->aStatusList, $this->iStatusFilter, \Yii::t('order', 'field_status'))
            ->filterDate('date', [$this->mDateFilter1, $this->mDateFilter2], \Yii::t('order', 'field_date'))
            ->filterText('filter_person', $this->sPersonFilter, \Yii::t('order', 'field_contact_face'))
            ->filterText('filter_id', $this->sIdFilter, 'ID')
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])
            ->field('date', \Yii::t('order', 'field_date'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('person', \Yii::t('order', 'field_contact_face'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('mail', \Yii::t('order', 'field_mail'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('total_price', \Yii::t('order', 'field_goods_total'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('status', \Yii::t('order', 'field_status'), 'string', ['listColumns' => ['flex' => 3]])
            ->fieldIf(
                (bool) \Yii::$app->hasModule('rest'),
                'is_mobile',
                'Mobile',
                'check',
                ['listColumns' => ['flex' => 1]]
            )
            ->widget('status', 'skewer\\build\\Adm\\Order\\Service', 'getStatusValue')
            ->setValue($this->aItems, $this->onPage, $this->page, $this->total)
            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->showCheckboxSelection()
            ->buttonEdit('statusList', \Yii::t('order', 'field_orderstatus_title'))
            ->buttonEdit('emailShow', \Yii::t('order', 'field_mailtext'))
            ->buttonEdit('editSettings', \Yii::t('order', 'settings'))
            ->buttonSeparator()
            ->buttonDeleteMultiple('deleteMultiple')
            ->buttonSeparator('->')
            ->buttonConfirm('deleteAllOrders', \Yii::t('adm', 'del_all'), \Yii::t('order', 'delAllOrders'), 'icon-delete');
    }
}
