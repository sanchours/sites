<?php

namespace skewer\build\Tool\Regions\view;

use skewer\base\site\Layer;
use skewer\components\ext\view\ListView;

class Labels extends ListView
{
    public $title;
    public $labels;

    public function build()
    {
        $this->_module->setPanelName($this->title, true);

        $this->_list
            ->field('id', \Yii::t('labels', 'id'), 'string', ['listColumns' => ['flex' => 1]])
            ->field('title', \Yii::t('labels', 'title'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('alias', \Yii::t('labels', 'alias'), 'string', ['listColumns' => ['flex' => 2]])
            ->field('default', \Yii::t('regions', 'value'), 'string', ['listColumns' => ['flex' => 5]])
            ->buttonCancel('Show')
            ->buttonRowUpdate('EditValueLabel')
            ->buttonRowCustomJs(
                'ParamsCleanObjBtn',
                Layer::TOOL,
                'Regions',
                [
                    'tooltip' => \Yii::t('regions', 'btn_clean'),
                    'actionText' => \Yii::t('regions', 'clean_confirm'),
                ]
            )
            ->setValue($this->labels);
    }
}
