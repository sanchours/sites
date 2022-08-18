<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 23.01.2017
 * Time: 10:05.
 */

namespace skewer\build\Tool\Review\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $bShowFieldType;
    public $bHasCatalogAndAccess;
    public $aStatusList;
    public $iStatusFilter;
    public $bIsGuestBookModule;
    public $iShowSection;
    public $aItems;
    public $iOnPage;
    public $iPage;
    public $iCount;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('id', 'ID', 'string', ['listColumns' => ['flex' => 1]])
            ->field('name', \Yii::t('review', 'field_name'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('date_time', \Yii::t('review', 'field_date_time'), 'date', ['listColumns' => ['flex' => 4]])
            //->field('email', \Yii::t('review', 'field_email'), 's', 'string',array( 'listColumns' => array('flex' => 3) ))
            ->field('content', \Yii::t('review', 'field_content'), 'string', ['listColumns' => ['flex' => 10]])
            ->fieldIf(
                $this->bShowFieldType,
                'type',
                \Yii::t('review', 'field_type'),
                'string',
                ['listColumns' => ['flex' => 2]]
            )
            ->field('link', \Yii::t('review', 'field_link'), 'show', ['listColumns' => ['flex' => 5]])
            ->field('status_text', \Yii::t('review', 'field_status'), 'string', ['listColumns' => ['flex' => 2]])

            ->buttonRowCustomJs('ApproveBtn', '', '', ['action' => 'approve-review', 'tooltip' => \Yii::t('review', 'approve'),])
            ->buttonRowCustomJs('RejectBtn', '', '', ['action' => 'reject-review', 'tooltip' => \Yii::t('review', 'reject'),])

            ->buttonEdit('settings', \Yii::t('review', 'settings'))
            ->widget('status_text', 'skewer\\build\\Tool\\Review\\Module', 'getStatusValue');

        if ($this->bHasCatalogAndAccess) {
            $this->_list->buttonEdit('settingsCatalog', \Yii::t('review', 'settings_catalog'));
        }

        $this->_list
            ->field('on_main', \Yii::t('review', 'field_show_main'), 'check', ['listColumns' => ['flex' => 3]])
            ->filterSelect('filter_status', $this->aStatusList, $this->iStatusFilter, \Yii::t('review', 'field_status_active'))
            ->buttonRowUpdate()
            ->buttonRowDelete();

        if ($this->bIsGuestBookModule) {
            $this->_list->buttonAddNew('show', \Yii::t('adm', 'add'), ['addParams' => ['show_section' => $this->iShowSection]]);
        }

        if ($this->iCount) {
            $this->_list->setValue($this->aItems, $this->iOnPage, $this->iPage, $this->iCount);
        } else {
            $this->_list->setValue($this->aItems);
        }

        $this->_list->setEditableFields(['on_main', 'carousel'], 'saveOnMain');
    }
}
