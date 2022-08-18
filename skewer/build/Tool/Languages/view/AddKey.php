<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 18.01.2017
 * Time: 17:24.
 */

namespace skewer\build\Tool\Languages\view;

use skewer\components\ext\view\FormView;

class AddKey extends FormView
{
    public $oParameters;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->fieldString('category', \Yii::t('languages', 'field_category'))
            ->fieldString('message', \Yii::t('languages', 'field_message'))
            ->fieldString('value', \Yii::t('languages', 'field_value'))
            ->fieldString('language', \Yii::t('languages', 'field_language'))
            ->setValue($this->oParameters)
            ->button('saveNewKey', \Yii::t('adm', 'save'), 'icon-save', 'init', [
                'addParams' => ['status' => 'newRow'],
            ])
            ->buttonBack('showKeys');
    }
}
