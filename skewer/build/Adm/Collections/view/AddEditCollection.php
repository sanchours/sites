<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 19.12.2016
 * Time: 16:28.
 */

namespace skewer\build\Adm\Collections\view;

use skewer\components\ext\view\FormView;

class AddEditCollection extends FormView
{
    public $sParamObj;
    public $sLayoutParamName;
    public $sLayoutTitleName;
    public $listTemplateMData;
    public $aCollectionsSections;
    public $aData;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('group')
            ->fieldHide('onMainCollection')
            ->fieldHide($this->sParamObj) // object
            ->fieldHide($this->sLayoutParamName) // layout
            ->fieldString($this->sLayoutTitleName, \Yii::t('Collections', 'column_title')) // .title
            ->fieldString('titleOnMain', \Yii::t('catalog', 'param_title_on_main'))
            ->fieldSelect('template', \Yii::t('Editor', 'type_category_view'), $this->listTemplateMData, [], false)
            ->fieldSelect('onMainCollectionSection', \Yii::t('Collections', 'field_collections'), $this->aCollectionsSections)
            ->buttonSave('SaveCollection')
            ->buttonCancel()
            ->setValue($this->aData);
    }
}
