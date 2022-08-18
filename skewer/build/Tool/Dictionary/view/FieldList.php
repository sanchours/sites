<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\base\site\Layer;
use skewer\components\ext\view\ListView;

class FieldList extends ListView
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
            ->fieldString('name', \Yii::t('dict', 'field_f_name'))
            ->fieldString('title', \Yii::t('dict', 'field_f_title'), ['listColumns.flex' => 1])
            ->fieldString('editor', \Yii::t('dict', 'field_f_editor'))
            ->widget('editor', 'skewer\\build\\Catalog\\CardEditor\\Api', 'applyEditorWidget')
            ->setValue($this->aFields)
            ->buttonAddNew('FieldEdit', \Yii::t('dict', 'btn_add_field'))
            ->buttonCancel('View', \Yii::t('dict', 'btn_back'))
            ->buttonRowUpdate('FieldEdit')
            ->buttonRowCustomJs('DelFieldBtn', Layer::CATALOG, 'CardEditor')
            ->enableDragAndDrop('sortFieldList');
    }
}
