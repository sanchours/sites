<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\components\catalog\model\FieldRow;
use skewer\components\ext\view\FormView;

class FieldEdit extends FormView
{
    public $sCardTitle;
    public $iFieldId;
    public $aSimpleTypeList;
    public $sTitleLinkId;
    public $aResult;

    /** @var FieldRow */
    public $oItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('dict', 'head_card_name', $this->sCardTitle) . '</h1>')
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('dict', 'field_f_title'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('dict', 'field_f_name'), ['disabled' => (bool) $this->iFieldId])
            ->fieldSelect('editor', \Yii::t('dict', 'field_f_editor'), $this->aSimpleTypeList, ['onUpdateAction' => 'UpdFieldLinkId'], false)
            ->fieldSelect('link_id', $this->sTitleLinkId, $this->aResult, ['hidden' => !$this->oItem->isLinked()], false)
            ->fieldString('def_value', \Yii::t('dict', 'field_f_def_value'))
            ->setValue($this->oItem)
            ->buttonSave('FieldSave')
            ->buttonCancel('FieldList');
    }
}
