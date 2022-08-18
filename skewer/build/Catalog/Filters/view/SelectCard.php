<?php

namespace skewer\build\Catalog\Filters\view;

use skewer\components\ext\view\FormView;

class SelectCard extends FormView
{
    public $aGoodsCardList;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('card_id', \Yii::t('filters', 'filter_card'), $this->aGoodsCardList, [], false)
            ->buttonSave('saveCard')
            ->buttonCancel('init');
    }
}
