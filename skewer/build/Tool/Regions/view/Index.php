<?php

namespace skewer\build\Tool\Regions\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $regions;

    public function build()
    {
        $this->_module->setPanelName(
            \Yii::t('regions', 'title_list_regions'),
            true
        );

        $this->_list
            ->field('id', 'ID', 'hide')
            ->field('domain', \Yii::t('regions', 'domain'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('utm', \Yii::t('regions', 'utm'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('city', \Yii::t('regions', 'city'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('region', \Yii::t('regions', 'region'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('fed_district', \Yii::t('regions', 'fed_district'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('active', \Yii::t('regions', 'active'), 'check', ['listColumns' => ['flex' => 1]])
            ->field('default', \Yii::t('regions', 'default'), 'check', ['listColumns' => ['flex' => 1]])
            ->buttonRowUpdate()
            ->buttonRowDelete()
            ->buttonAddNew('AddRegion', \Yii::t('regions', 'btn_add'))
            ->button('Settings', \Yii::t('regions', 'settings'), 'icon-edit')

            ->setEditableFields(['active'], 'changeActive')

            ->setValue($this->regions);
    }
}
