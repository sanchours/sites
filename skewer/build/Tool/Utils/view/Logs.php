<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 13.02.2017
 * Time: 11:08.
 */

namespace skewer\build\Tool\Utils\view;

use skewer\components\ext\docked\Api;
use skewer\components\ext\view\FormView;

class Logs extends FormView
{
    public $sText;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        if ($this->sText) {
            $this->_form
                ->field('text', 'text', 'show', ['hideLabel' => 1])
                ->setValue(['text' => $this->sText]);
        }
        $this->_form
            ->button('viewAccess', \Yii::t('utils', 'view_access'), Api::iconReload, 'viewAccess')
            ->button('viewError', \Yii::t('utils', 'view_error'), Api::iconReload, 'viewError')
            ->button('clearLogs', \Yii::t('utils', 'clear_logs'), Api::iconDel, 'clearLogs')
            ->button('init', \Yii::t('adm', 'back'), Api::iconCancel, 'init');
    }
}
