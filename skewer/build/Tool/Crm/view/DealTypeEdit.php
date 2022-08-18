<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.01.2017
 * Time: 11:32.
 */

namespace skewer\build\Tool\Crm\view;

use skewer\build\Tool\Crm\models\DealType;
use skewer\components\ext\view\FormView;

class DealTypeEdit extends FormView
{
    /**
     * @var DealType
     */
    public $oDealType;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id')
            ->fieldString('name', \Yii::t('crm', 'name_site'))
            ->fieldShow('name_crm', \Yii::t('crm', 'name_crm'))
            ->fieldCheck('active', \Yii::t('crm', 'active'))
            ->setValue($this->oDealType)
            ->buttonSave('saveDealType')
            ->buttonCancel('DealTypeList');
    }
}
