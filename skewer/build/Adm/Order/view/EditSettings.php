<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 05.12.2016
 * Time: 18:01.
 */

namespace skewer\build\Adm\Order\view;

use skewer\components\ext\view\FormView;

class EditSettings extends FormView
{
    public $aItems;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form->fieldInt('order_max_size', \Yii::t('order', 'field_order_max_size'), ['minValue' => 0])
            ->fieldInt('onpage_goods', \Yii::t('order', 'field_onpage_goods'), ['minValue' => 0])
            ->fieldInt('onpage_profile', \Yii::t('order', 'field_onpage_profile'), ['minValue' => 0])
            ->fieldInt('onpage_cms', \Yii::t('order', 'field_onpage_cms'), ['minValue' => 0])
            ->setValue($this->aItems)
            ->buttonSave('saveSettings')
            ->buttonCancel('list');
    }
}
