<?php
/**
 * Created by PhpStorm.
 * User: ram
 * Date: 15.05.2018
 * Time: 16:07.
 */

namespace skewer\build\Tool\SeoGen\view;

use skewer\components\ext\view\FormView;

class Index extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->button('importState', 'Импорт', 'icon-configuration')
            ->button('exportState', 'Экспорт', 'icon-configuration');
    }
}
