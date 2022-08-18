<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.01.2017
 * Time: 11:32.
 */

namespace skewer\build\Tool\Crm\view;

use skewer\build\Tool\Crm\models\DealEvent;
use skewer\components\ext\view\FormView;

class DealEventEdit extends FormView
{
    /**
     * @var DealEvent
     */
    public $oDealEvent;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if (!$this->oDealEvent['from']) {
            $this->oDealEvent['from'] = \Yii::t('crm', 'empty');
        }
        if (!$this->oDealEvent['to']) {
            $this->oDealEvent['to'] = \Yii::t('crm', 'empty');
        }

        $this->_form
            ->fieldHide('id')
            ->fieldString('title', \Yii::t('crm', 'name'))
            ->fieldShow('title_crm', \Yii::t('crm', 'name_crm'))
            ->fieldShow('from', \Yii::t('crm', 'from'))
            ->fieldShow('to', \Yii::t('crm', 'to'))
            ->fieldCheck('active', \Yii::t('crm', 'active'))
            ->setValue($this->oDealEvent)
            ->buttonSave('saveDealEvent')
            ->buttonCancel('DealEventList');
    }
}
