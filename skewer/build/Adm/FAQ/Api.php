<?php

namespace skewer\build\Adm\FAQ;

class Api
{
    /**
     * Отдает набор доступных статусов.
     *
     * @return array
     */
    public static function getStatusList()
    {
        return [
            models\Faq::statusNew => \Yii::t('faq', 'new'),
            models\Faq::statusApproved => \Yii::t('faq', 'approved'),
            models\Faq::statusRejected => \Yii::t('faq', 'rejected'),
        ];
    }
}
