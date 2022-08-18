<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 29.12.2016
 * Time: 17:34.
 */

namespace skewer\build\Catalog\CardEditor\view;

use skewer\base\site\Layer;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext\view\ListView;

class FieldList extends ListView
{
    public $sHeadText;
    public $aFields;
    public $bIsExtendedCard;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->headText(sprintf('<h1>%s</h1>', $this->sHeadText))
            //->fieldHide( 'id', 'id' )
            ->fieldString('name', \Yii::t('card', 'field_f_name'), ['listColumns.flex' => 1])
            ->fieldString('title', \Yii::t('card', 'field_f_title'), ['listColumns.flex' => 1])
            //->fieldString( 'group', \Yii::t('card', 'field_f_group') )
            ->fieldString('editor', \Yii::t('card', 'field_f_editor'))
            ->setGroups('group')
            ->widget('group', 'skewer\\build\\Catalog\\CardEditor\\Api', 'applyGroupWidget')
            ->widget('editor', 'skewer\\build\\Catalog\\CardEditor\\Api', 'applyEditorWidget')
            ->setValue($this->aFields)
            ->buttonAddNew('FieldEdit', \Yii::t('card', 'btn_add_field'))
            ->buttonEdit('CardEdit', \Yii::t('card', 'btn_params'))
            ->buttonCancel('CardList', \Yii::t('card', 'btn_back'));
        if ($this->bIsExtendedCard) {
            $this->_list
                ->buttonSeparator('->')
                ->buttonConfirm('CardRemove', \Yii::t('adm', 'del'), \Yii::t('card', 'remove_card_msg'), 'icon-delete');
        }

        if (CurrentAdmin::isSystemMode()) {
            $this->_list->buttonRowUpdate('FieldEdit');
        } else {
            $this->_list->buttonRowCustomJs('EditFieldBtn', '', '', ['state' => 'edit_form']);
        }

        $this->_list
            ->buttonRowCustomJs('DelFieldBtn', Layer::CATALOG, 'CardEditor')
            ->enableDragAndDrop('sortFields');
    }
}
