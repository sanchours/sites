<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.01.2017
 * Time: 12:17.
 */

namespace skewer\build\Tool\Domains\view;

use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\ListView;

class Index extends ListView
{
    public $aItems;
    public $bNotInCluster;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if (!INCLUSTER) {
            $this->_list
                ->fieldCheck('prim', \Yii::t('domains', 'module_main_domain'), ['listColumns' => ['width' => 64]])
                ->setEditableFields(['prim'], 'updFromList');
        }

        $this->_list
            ->fieldString('domain', \Yii::t('domains', 'module_domain_name'), ['listColumns' => ['flex' => 1]]);

        if (!INCLUSTER && CurrentAdmin::isSystemMode()) {
            $this->_list
                ->buttonRowDelete();
        }

        $this->_list->setValue($this->aItems);

        if ($this->bNotInCluster) {
            $this->_list->buttonAddNew('showForm');
        }
    }
}
