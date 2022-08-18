<?php

namespace skewer\build\Adm\Gallery\view;

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
                \Yii::t('gallery', 'albums'),
                'icon-edit',
                'popup_window'
            );
    }
}
