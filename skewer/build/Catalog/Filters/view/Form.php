<?php

namespace skewer\build\Catalog\Filters\view;

use skewer\build\Catalog\Filters\model\FilterSettings4Card;
use skewer\components\ext\view\FormView;

class Form extends FormView
{
    /** @var FilterSettings4Card */
    public $item;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldHide('id', 'ID')
            ->fieldHide('card_id', 'CardID')
            ->fieldString('title', 'Название', ['disabled' => true])
            ->fieldString('alt_title', 'Альтернативный заголовок')
            ->fieldString('meta_title', 'MetaTitle')
            ->fieldText('meta_description', 'MetaDescription')
            ->fieldText('meta_keywords', 'MetaKeywords')
            ->fieldWysiwyg('staticContent1', 'Текст раздела 1', 400)
            ->fieldShow('info', 'Разрешенные метки')

            ->buttonSave('saveFilterSettings4Card')
            ->buttonCancel('init');

        $this->_form->setValue($this->item);
    }
}
