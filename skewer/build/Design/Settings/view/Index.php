<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 17.03.2017
 * Time: 18:01.
 */

namespace skewer\build\Design\Settings\view;

use skewer\components\ext\view\FormView;

class Index extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->button('settingsForm', 'Настройки')
            ->button('CategoryViewerForm', 'Шаблон списка разделов')
            ->button('FormTplForm', 'Шаблон форм');
    }
}
