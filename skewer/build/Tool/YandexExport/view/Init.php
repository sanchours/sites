<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 17.02.2017
 * Time: 10:10.
 */

namespace skewer\build\Tool\YandexExport\view;

use skewer\components\ext\view\FormView;

class Init extends FormView
{
    public $sRunExportTitle;
    public $sHeadText;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonAddNew(
                'runExport',
                $this->sRunExportTitle
            )
            ->buttonEdit('settings', \Yii::t('yandexExport', 'settings'))
            ->button('showTask', \Yii::t('yandexExport', 'task_form'), 'icon-configuration')
            ->buttonEdit('Utils', \Yii::t('yandexExport', 'utils'))
            ->headText($this->sHeadText);
    }
}
