<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 21.11.2016
 * Time: 17:18.
 */

namespace skewer\build\Tool\Slider\view;

use skewer\components\ext\view\FormView;

class editSlideText extends FormView
{
    public $oItem;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'id', 'hide')
            ->fieldWithValue('back2edit', '', 'hide', true)
            ->field('text1', \Yii::t('slider', 'slides_text1'), 'wyswyg')
            ->field('text2', \Yii::t('slider', 'slides_text2'), 'wyswyg')
            ->field('text3', \Yii::t('slider', 'slides_text3'), 'wyswyg')
            ->field('text4', \Yii::t('slider', 'slides_text4'), 'wyswyg')
            ->setValue($this->oItem)
            ->buttonSave('saveSlide')
            ->buttonCancel('EditSlideForm');
    }
}
