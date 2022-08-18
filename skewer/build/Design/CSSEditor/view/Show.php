<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 12.01.2017
 * Time: 15:37.
 */

namespace skewer\build\Design\CSSEditor\view;

use skewer\components\ext\view\FormView;

class Show extends FormView
{
    public $aData;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide', ['listColumns' => ['flex' => 1]])
            ->field('name', \Yii::t('design', 'field_name'), 'string', ['listColumns' => ['flex' => 3]]);
        $this->_form
            ->field('last_upd', \Yii::t('design', 'field_last_upd'), 'hide')
            ->field('active', \Yii::t('design', 'field_active'), 'check');
        $this->_form->field('data', \Yii::t('design', 'field_data'), 'text_css', [
                'hideLabel' => true,
                'heightInPanel' => true,
                'height' => '92%',
                'margin' => '0 1 0 0',
            ])
            ->buttonSave();
        $this->_form->buttonBack()
            ->setValue($this->aData);
    }
}
