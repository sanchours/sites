<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 31.01.2017
 * Time: 19:02.
 */

namespace skewer\build\Tool\Redirect301\view;

use skewer\build\Tool\Redirect301\Api;
use skewer\components\ext\view\FormView;

class Export extends FormView
{
    public $aParams;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->buttonCancel()
            ->fieldLink('out_file', \Yii::t('Redirect301', 'out_file'), 'Redirects.xls', Api::getLinkExportFile());
    }
}
