<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 29.12.2016
 * Time: 17:52.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\components\auth\CurrentAdmin;
use skewer\components\catalog\model\FieldRow;
use skewer\components\ext\view\FormView;

class FieldEdit extends FormView
{
    public $sHeadText;
    public $iFieldId;
    public $aGroupList;
    public $aSimpleTypeList;
    /** @var FieldRow */
    public $oField;
    public $aEntityList;
    public $aSimpleWidgetList;
    public $aListWithTitles;
    public $bIsSystemFieldInBaseCard;
    public $aProtectedParams;
    public $aAttrList = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $disableEdit = $this->oField->disableEdit();

        $this->_form
            ->headText(sprintf('<h1>%s</h1>', $this->sHeadText))
            ->fieldHide('id', 'id')
            ->fieldString('title', \Yii::t('card', 'field_f_title'), ['listColumns.flex' => 1])
            ->fieldString('name', \Yii::t('card', 'field_f_name'), ['disabled' => (bool) $this->iFieldId])
            ->fieldSelect('group', \Yii::t('card', 'field_f_group'), $this->aGroupList, [], false)
            ->fieldSelect('editor', \Yii::t('card', 'field_f_editor'), $this->aSimpleTypeList, ['onUpdateAction' => 'updFields', 'disabled' => $disableEdit], false)
            ->fieldSelect('link_id', \Yii::t('card', 'field_f_link_id'), $this->aEntityList, ['onUpdateAction' => 'updFields', 'disabled' => !$this->oField->isLinked() || $disableEdit], false)
            ->fieldSelect('widget', \Yii::t('card', 'field_f_widget'), $this->aSimpleWidgetList)
            ->fieldMultiSelect('validator', \Yii::t('card', 'field_f_validator'), $this->aListWithTitles, $this->oField->getValidatorList())
            ->fieldString('def_value', \Yii::t('card', 'field_f_def_value'));

        foreach ($this->aAttrList as $aAttrParam) {
            $this->_form->fieldWithValue('attr_' . $aAttrParam['id'], \Yii::t('catalog', 'attr_' . $aAttrParam['name']), $aAttrParam['type'], $aAttrParam['value']);
        }

        if ($this->bIsSystemFieldInBaseCard) {
            foreach ($this->aProtectedParams as $sProtectedParam) {
                if ($oFieldForm = $this->_form->getField($sProtectedParam)) {
                    $oFieldForm->setDescVal('disabled', true);
                }
            }
        }

        if (CurrentAdmin::isSystemMode()) {
            $this->_form->fieldCheck('prohib_del', \Yii::t('card', 'prohib_del'), ['default' => 0]);
            $this->_form->fieldCheck('no_edit', \Yii::t('card', 'field_no_edit'));
        }

        $this->_form
            ->setValue($this->oField)
            ->buttonSave('FieldSave')
            ->buttonCancel('FieldList');
    }
}
