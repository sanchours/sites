<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 06.03.2017
 * Time: 17:24.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\components\ext\view\ListView;

class Structure extends ListView
{
    public $sHeadText;
    public $aFields;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_list
            ->headText(sprintf('<h1>%s</h1>', $this->sHeadText))
            ->fieldShow('id', 'id')
            ->fieldString('name', \Yii::t('card', 'field_f_name'))
            ->fieldString('title', \Yii::t('card', 'field_f_title'), ['listColumns.flex' => 1])
            ->fieldString('editor', \Yii::t('card', 'field_f_editor'))
            ->widget('editor', 'skewer\\build\\Catalog\\CardEditor\\Api', 'applyEditorWidget')
            ->setValue($this->aFields)
            ->buttonAddNew('FieldEdit', \Yii::t('card', 'btn_add_field'))
            ->buttonCancel('View', \Yii::t('card', 'btn_back'))
            ->buttonRowUpdate('FieldEdit')
            ->buttonRowDelete('FieldRemove');
    }
}
