<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 12:42.
 */

namespace skewer\build\Tool\HttpsMode\view;

use skewer\components\ext\view\FormView;

class Index extends FormView
{
    public $bActiveHTTPS;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('https_enable', '', 'show', [
                'hideLabel' => true,
                'fieldStyle' => 'font-size: 14px; float: none; text-align: center;',
            ]);

        $this->_form
            ->field('https_status', '', 'show', [
                'hideLabel' => true,
                'fieldStyle' => 'font-size: 14px; float: none; text-align: center;',
            ])
            ->setValue([
                'https_enable' => ($this->bActiveHTTPS) ? \Yii::t('HttpsMode', 'https_on') : \Yii::t('HttpsMode', 'https_off'),
            ]);

        // Кнопка включения протокола https
        if ($this->bActiveHTTPS) {
            $this->_form->button('stopHttps', \Yii::t('HttpsMode', 'button_https_off'), 'icon-stop', 'init');
        } else {
            $this->_form->button('startHttps', \Yii::t('HttpsMode', 'button_https_on'), 'icon-visible');
        }
    }
}
