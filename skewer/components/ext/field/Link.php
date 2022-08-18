<?php

namespace skewer\components\ext\field;

/**
 * Редактор "Ссылка".
 */
class Link extends Prototype
{
    public function getView()
    {
        return 'show';
    }

    /** {@inheritdoc} */
    public function getDesc()
    {
        $this->setLink($this->getDescVal('show_val', ''), $this->getValue());

        return parent::getDesc();
    }

    /**
     * Сформировать ссылку.
     *
     * @param string $sText Текст ссылки
     * @param string $sHref Ссылка
     */
    private function setLink($sText, $sHref)
    {
        parent::setValue("<a href=\"{$sHref}\" target=\"_blank\">{$sText}</a>");
    }
}
