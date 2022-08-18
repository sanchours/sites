<?php


namespace skewer\build\Tool\Policy\view;

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
