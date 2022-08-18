<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 14.05.2018
 * Time: 10:55.
 */

namespace skewer\build\Tool\Profile\view;

use skewer\components\ext\view\FormView;

class Form extends FormView
{
    /** @var array */
    public $values;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonSave('saveWishListParams')
            ->buttonCancel()
            ->fieldCheck('enableModule', \Yii::t('profile', 'wishlist_status'))
            ->fieldInt('OnPage', \Yii::t('profile', 'wishlist_onpage'))
            ->fieldSelect('ShowMod', \Yii::t('profile', 'wishlist_show'), ['List' => \Yii::t('profile', 'type_view_list')], [], false)
            ->setValue($this->values);
    }
}
