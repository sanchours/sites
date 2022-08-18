<?php

/**
 * Created by PhpStorm.
 * User: holod
 * Date: 16.02.2017
 * Time: 18:19.
 */

namespace skewer\build\Tool\YandexExport\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $aValue;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('shopName', \Yii::t('yandexExport', 'shopName'), 'string')
            ->field('companyName', \Yii::t('yandexExport', 'companyName'), 'string')
            ->field('localDeliveryCost', \Yii::t('yandexExport', 'localDeliveryCost'), 'string')
            ->setValue($this->aValue)
            ->buttonSave('saveSettings')
            ->buttonBack();
    }
}
