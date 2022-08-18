<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 10.01.2017
 * Time: 16:17.
 */

namespace skewer\build\Catalog\Goods\view;

use skewer\components\ext\view\FormView;

class SetCard extends FormView
{
    public $aGoodsCardList;
    public $sCardName;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('card', \Yii::t('catalog', 'section_goods'), $this->aGoodsCardList, [], false)
            ->setValue(['card' => $this->sCardName])
            ->buttonSave('saveCard');
    }
}
