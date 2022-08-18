<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 20.01.2017
 * Time: 11:31.
 */

namespace skewer\build\Tool\ReachGoal\view;

use skewer\components\ext\view\ListView;

class ShowSelectors extends ListView
{
    public $aSelectors;

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->_list
            ->field('title', \Yii::t('ReachGoal', 'field_title_selector'), 'string', ['listColumns' => ['flex' => 3]])
            ->field('selector', \Yii::t('ReachGoal', 'field_selector'), 'string', ['listColumns' => ['flex' => 3]])
            ->buttonRowUpdate('showSelector')
            ->buttonRowDelete('DeleteSelector')
            ->buttonAddNew('addSelector', \Yii::t('ReachGoal', 'btn_add_selector'))
            ->buttonBack('Init')
            ->setValue($this->aSelectors);
    }
}
