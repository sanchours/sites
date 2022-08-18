<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.01.2017
 * Time: 17:31.
 */

namespace skewer\build\Tool\Subscribe\view;

use skewer\components\ext\view\FormView;

class SendSubscribeForm extends FormView
{
    public $aTempItems;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->headText('<h1>' . \Yii::t('subscribe', 'sending') . '</h1>')
            ->field('id', 'ID', 'hide')
            ->field('title', \Yii::t('subscribe', 'title'), 'show')
            ->field('text', \Yii::t('subscribe', 'text'), 'show')
            ->field('test_mail', \Yii::t('subscribe', 'test_email_title'), 'string', ['subtext' => \Yii::t('subscribe', 'testMail')])
            ->setValue($this->aTempItems)
            ->button('sendSubscribe', \Yii::t('subscribe', 'sendSubscribers'), 'icon-commit', 'allow_do', ['actionText' => \Yii::t('subscribe', 'sendSubscribersText')])
            ->button('sendToEmailSubscribe', \Yii::t('subscribe', 'testMailText'), 'icon-commit', 'init', ['doNotUseTimeout' => true])
            ->buttonCancel()
            ->setTrackChanges(false);
    }
}
