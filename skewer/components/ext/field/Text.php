<?php

namespace skewer\components\ext\field;

/**
 * Редактор "текст".
 */
class Text extends Prototype
{
    /** {@inheritdoc} */
    public function getView()
    {
        return 'text';
    }

    /** {@inheritdoc} */
    public function getDesc()
    {
        $this->processParams();

        return parent::getDesc();
    }

    /** {@inheritdoc} */
    public function setValue($mValue)
    {
        if ($this->getDescVal('show_val', null) !== null) {
            $this->setDescVal('show_val', $mValue);
        } else {
            parent::setValue($mValue);
        }
    }

    /** {@inheritdoc} */
    public function getValue()
    {
        if ($this->getDescVal('show_val', null) !== null) {
            return $this->getDescVal('show_val');
        }

        return parent::getValue();
    }

    /** Обработать параметры поля */
    protected function processParams()
    {
        if (($mShowVal = $this->getDescVal('show_val', null)) !== null) {
            // Установка высоты поля
            if ($iHeight = (int) parent::getValue()) {
                $this->setDescVal('height', $iHeight);
            }

            // Установка значения из парамтра show_val
            $this->delDescVal('show_val');
            parent::setValue($mShowVal);
        }
    }
}
