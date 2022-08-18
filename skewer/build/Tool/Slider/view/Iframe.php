<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 14:59.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\components\ext\view\IframeView;

class Iframe extends IframeView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->button(
                'show',
                \Yii::t('slider', 'displaySettings'),
                'icon-edit',
                'popup_window'
            );
    }
}
