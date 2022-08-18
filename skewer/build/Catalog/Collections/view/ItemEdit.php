<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.12.2016
 * Time: 17:54.
 */

namespace skewer\build\Catalog\Collections\view;

use skewer\components\catalog;
use skewer\components\ext\view\FormView;
use skewer\components\seo;

class ItemEdit extends FormView
{
    public $aFields;
    /** @var catalog\CatalogGoodRow */
    public $oItem;
    public $sClassName;
    public $sEntityIdVal;
    public $id;
    public $oSeoElemCollection;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form->fieldHide('id', 'id');
        foreach ($this->aFields as $oField) {
            if ($oField->name !== 'last_modified_date') {
                $this->_form->fieldByEntity($oField);
            }
        }
        $this->_form
            ->setValue($this->oItem)
            ->buttonSave('ItemSave')
            ->buttonCancel('View');
        $this->_form->getField('gallery')->setDescVal('seoClass', $this->sClassName);
        $this->_form->getField('gallery')->setDescVal('iEntityId', $this->sEntityIdVal);
        if ($this->id) {
            $this->_form
                ->buttonSeparator()
                ->button('GoodsView', \Yii::t('collections', 'goods'), 'icon-view');
        }

        seo\Api::appendExtForm($this->_form, $this->oSeoElemCollection, 0, ['seo_gallery', 'none_search', 'none_index', 'add_meta'], false);
    }
}
