<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 17.03.2017
 * Time: 18:01.
 */

namespace skewer\build\Design\Settings\view;

use skewer\components\ext\view\FormView;

class Settings extends FormView
{
    public $bDeleteParams;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldCheck('DeleteParams', 'Разрешить удаление параметров')
            ->buttonSave('saveSettings')
            ->buttonCancel();

        $this->_form->setValue([
            'DeleteParams' => $this->bDeleteParams,
        ]);
    }
}
