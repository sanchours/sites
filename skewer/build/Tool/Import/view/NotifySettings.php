<?php

namespace skewer\build\Tool\Import\view;

use skewer\components\ext\view\FormView;
use Yii;

/**
 * Class NotifySettings
 * @package skewer\build\Tool\Import\view
 */
class NotifySettings extends FormView
{
    public $items = [];

    function build()
    {
        $this->_form
            ->fieldShow('info', '', 'show',  ['hideLabel' => true])
            ->fieldCheck('mail_notify_is_send', Yii::t('import', 'mail_notify_is_send'))
            ->fieldString('mail_notify_mail_to', Yii::t('import', 'mail_settings_mail_notify_mail_to'))
            ->fieldString('mail_notify_title', Yii::t('import', 'mail_settings_mail_notify_title'))
            ->fieldWysiwyg('mail_notify_body', Yii::t('import', 'mail_settings_mail_notify_body'))
            ->setValue($this->items)
            ->buttonSave('notifySettingsSave')
            ->buttonBack('list');
    }
}
