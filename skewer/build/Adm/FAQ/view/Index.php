<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.11.2016
 * Time: 11:14.
 */

namespace skewer\build\Adm\FAQ\view;

use skewer\build\Adm\FAQ\Api;
use skewer\build\Adm\FAQ\models\Faq;
use skewer\components\ext\view\ListView;

class Index extends ListView
{
    /** @var Faq[] */
    public $items = [];

    public $filterStatus;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        /* Фильтр по статусу */
        $this->_list
            ->filterSelect('filter_status', Api::getStatusList(), $this->filterStatus, \Yii::t('faq', 'status'));

        /* Добавляем поля для списка */
        $this->_list
            ->fieldString('name', \Yii::t('faq', 'name'))
            ->fieldString('date_time', \Yii::t('faq', 'date_time'))
            ->fieldString('content', \Yii::t('faq', 'content'), ['listColumns' => ['flex' => 3]])
            ->fieldString('answer', \Yii::t('faq', 'answer'), ['listColumns' => ['flex' => 3]])
            ->fieldString('status', \Yii::t('faq', 'status'))

            /* для статуса */
            ->widget('status', 'skewer\\build\\Adm\\FAQ\\Service', 'getStatusValue')

             /* кнопки в записи */
            ->buttonRowUpdate()
            ->buttonRowDelete('delete')

            /* кнопки общие */
            ->buttonAddNew('new', \Yii::t('faq', 'add'))
            ->buttonEdit('settings', \Yii::t('faq', 'settings'));

        $this->_list->setValue($this->items, $this->onPage, $this->page, $this->total);
    }
}
