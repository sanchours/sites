<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 16.05.2018
 * Time: 11:56.
 */

namespace skewer\build\Adm\Catalog\view;

use skewer\components\ext\view\FormView;

class SetSearchCard extends FormView
{
    /** @var array */
    public $goodsCardList;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('searchCard', \Yii::t('catalog', 'good_card'), $this->goodsCardList, [], false)
            ->setValue([])
            ->buttonSave('saveConfig');
    }
}
