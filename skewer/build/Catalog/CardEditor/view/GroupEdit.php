<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 30.12.2016
 * Time: 11:53.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\components\ext\view\FormView;

class GroupEdit extends FormView
{
    public $iGroupId;
    public $oGroup;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('card', 'field_g_title'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('card', 'field_g_name'), ['disabled' => (bool) $this->iGroupId])
            ->fieldSelect('group_type', \Yii::t('card', 'field_g_type'), [
                \Yii::t('card', 'field_g_type_default'),
                \Yii::t('card', 'field_g_type_collapsible'),
                \Yii::t('card', 'field_g_type_collapsed'),
            ], [], false)
            ->setValue($this->oGroup)
            ->buttonSave('GroupSave')
            ->buttonCancel('GroupList');
    }
}
