<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 15:41.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class EditSubscribe extends FormView
{
    public $sTextInfoBlock;
    public $bTypeView;
    public $aStatus;
    public $aTempItems;
    public $iStatusFormation;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('subscribe', 'editSubscribe') . '</h1>' .
                '<div>' . $this->sTextInfoBlock . '</div>')
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('subscribe', 'title'), ($this->bTypeView) ? 'string' : 'show')
            ->field('text', \Yii::t('subscribe', 'text'), ($this->bTypeView) ? 'wyswyg' : 'show')
            ->fieldSelect('status', \Yii::t('subscribe', 'status'), $this->aStatus, ['disabled' => true])
            ->field('template', \Yii::t('subscribe', 'template'), 'hide')
            ->setValue($this->aTempItems);

        if (isset($this->aTempItems['status']) && $this->aTempItems['status'] == $this->iStatusFormation) {
            $this->_form
                ->button('sendSubscribeForm', \Yii::t('subscribe', 'send'), 'icon-commit', 'init')
                ->buttonSeparator()
                ->buttonSave('saveSubscribe');
        }
        $this->_form->useSpecSectionForImages();
        $this->_form->buttonCancel('list');
    }
}
