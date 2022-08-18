<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 13:30.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class EditTemplate extends FormView
{
    public $sTextInfoBlock;
    public $aSubscribeTemplate;
    public $bModeMultiTemplates;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('subscribe', 'editTemplateH1') . '</h1>')
            ->headText('<h1>' . \Yii::t('subscribe', 'editTemplateH1') . '</h1>'
                . '<div>' . $this->sTextInfoBlock . '</div>')
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('subscribe', 'title'), 'string')
            ->field('content', \Yii::t('subscribe', 'text'), 'wyswyg')
            ->setValue($this->aSubscribeTemplate);
        $this->_form->useSpecSectionForImages();
        $this->_form->buttonSave('saveTemplate');

        if ($this->bModeMultiTemplates) {
            $this->_form->buttonCancel('templates');
        } else {
            $this->_form->buttonCancel('users');
        }
    }
}
