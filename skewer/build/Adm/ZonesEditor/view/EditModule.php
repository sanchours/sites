<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.12.2016
 * Time: 13:13.
 */

namespace skewer\build\Adm\ZonesEditor\view;

use skewer\components\ext\view\FormView;

class EditModule extends FormView
{
    public $aInstallableModules;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldString('title', \Yii::t('ZonesEditor', 'module_title'))
            ->fieldString('name', \Yii::t('ZonesEditor', 'module_name'))
            ->fieldSelect('paramSettingsClass', \Yii::t('ZonesEditor', 'module_type'), $this->aInstallableModules)
            ->buttonSave('installModule')
            ->buttonCancel('init');
    }
}
