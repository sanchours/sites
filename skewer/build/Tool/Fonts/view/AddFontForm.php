<?php

namespace skewer\build\Tool\Fonts\view;

use skewer\components\ext\view\FormView;
use skewer\components\fonts\Api;

class AddFontForm extends FormView
{
    /** @var \skewer\components\fonts\models\Fonts */
    public $item;

    public $aDirs = [];

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->field('id', 'ID', 'hide')
            ->field('name', 'Название семейства шрифтов', 'string', ['subtext' => 'Укажите font-family без кавычек и дефолтного шрифта. Пример - PT Sans'])
            ->fieldSelect('path', 'Директория со шрифтами', $this->aDirs, ['subtext' => 'Относительно /web/files/fonts/'], false)
            ->fieldSelect('fallback', 'Семейство по умолчанию', Api::getListFallback(), [], true)

            ->buttonSave('saveFont')
            ->buttonBack();

        if ($this->item->id && $this->item->type == Api::TYPE_FONT_EXTERNAL) {
            $this->_form
                ->buttonSeparator('->')
                ->buttonDelete();
        }

        $this->_form->setValue($this->item->getAttributes());
    }
}
