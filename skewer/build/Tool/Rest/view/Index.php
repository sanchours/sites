<?php

namespace skewer\build\Tool\Rest\view;

use skewer\components\ext\view\FormView;


class Index extends FormView
{
    public $apiKey;


    public function build()
    {
        $this->_form
            ->fieldShow('apiKey', \Yii::t('rest', 'label_api_key'), 's', ['value' => $this->apiKey]);
    }
}
