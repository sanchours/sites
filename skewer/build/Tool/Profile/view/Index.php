<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 14.05.2018
 * Time: 10:33.
 */

namespace skewer\build\Tool\Profile\view;

use skewer\components\ext\view\ListView;

class Index extends ListView
{
    /** @var bool CurrentAdmin::isSystemMode() */
    public $isSys;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->isSys) {
            $this->_list->button('wishList', \Yii::t('profile', 'wishlist'), 'icon-configuration', 'init');
        }
    }
}
