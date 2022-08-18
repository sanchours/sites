<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 15:28.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class AddSubscribeStep2 extends FormView
{
    public $sTextInfoBlock;
    public $aStatus;
    public $aItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('subscribe', 'editSubscribeText') . '</h1>' .
                '<div>' . $this->sTextInfoBlock . '</div>')
            ->field('id', 'id', 'hide')
            ->field('title', \Yii::t('subscribe', 'title'), 'string')
            ->field('text', \Yii::t('subscribe', 'text'), 'wyswyg')
            ->fieldSelect('status', \Yii::t('subscribe', 'status'), $this->aStatus, ['disabled' => true], false)
            ->field('template', \Yii::t('subscribe', 'template'), 'hide')
            ->setValue($this->aItems)
            ->buttonSave('saveSubscribe')
            ->buttonCancel('addSubscribeStep1')
            ->useSpecSectionForImages();
    }
}
