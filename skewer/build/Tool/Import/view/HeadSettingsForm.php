<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 15:36.
 */

namespace skewer\build\Tool\Import\view;

use skewer\components\auth\CurrentAdmin;
use skewer\components\import\Api;

class HeadSettingsForm extends ImportForm
{
    public $sGroup;
    public $aCardList;
    public $aProviderTypeList;
    public $aTypeList;
    public $aCodingList;
    public $aData;
    public $id;
    public $isNewAdmin;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide', ['groupTitle' => $this->sGroup])
            ->field('title', \Yii::t('import', 'field_title'), 'string', ['groupTitle' => $this->sGroup])
            ->fieldSelect('card', \Yii::t('import', 'field_card'), $this->aCardList, ['groupTitle' => $this->sGroup], false)

            ->fieldSelect('provider_type', \Yii::t('import', 'field_provider_type'), $this->aProviderTypeList, ['groupTitle' => $this->sGroup], false)

            ->fieldSelect('type', \Yii::t('import', 'field_type'), $this->aTypeList, ['onUpdateAction' => 'updFieldsSource', 'customField' => 'ImportType', 'groupTitle' => $this->sGroup], false)

            ->field('source_file', \Yii::t('import', 'field_source'), $this->aData['type'] != Api::Type_File && $this->isNewAdmin ? 'hide' : 'file', ['groupTitle' => $this->sGroup])
            ->field('source_str', \Yii::t('import', 'field_source'), $this->aData['type'] == Api::Type_File && $this->isNewAdmin ? 'hide' : 'string', ['groupTitle' => $this->sGroup])

            ->fieldSelect('coding', \Yii::t('import', 'field_coding'), $this->aCodingList, ['groupTitle' => $this->sGroup], false)
            ->fieldCheck('use_dict_cache', \Yii::t('import', 'use_dict_cache'), ['groupTitle' => $this->sGroup])
            ->fieldCheck('use_goods_hash', \Yii::t('import', 'use_goods_hash'), ['groupTitle' => $this->sGroup])
            ->fieldCheck('send_notify', \Yii::t('import', 'field_send_notify'), ['groupTitle' => $this->sGroup])
            ->fieldCheck('send_error', \Yii::t('import', 'send_error'), ['groupTitle' => $this->sGroup])
            ->fieldCheck('clear_log', \Yii::t('import', 'field_clear_log'), ['groupTitle' => $this->sGroup])

            ->addLib('ImportType')
            ->setValue($this->aData);

        if ($this->id) {
            $this->_form->buttonSave();
            $this->addStateButton('headSettings');
        } else {
            $this->_form
                ->buttonSave('save')
                ->buttonCancel('list');
        }

        if (CurrentAdmin::isSystemMode()) {
            $this->_form->buttonSeparator('->');
            $this->_form->button('clearQueue', \Yii::t('import', 'button_label_clearQueue'), 'icon-delete');
        }

        $this->_form->setTrackChanges(false);
    }
}
