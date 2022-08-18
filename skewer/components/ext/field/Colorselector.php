<?php

namespace skewer\components\ext\field;

class Colorselector extends Select
{
    /** {@inheritdoc} */
    public function getView()
    {
        return 'colorselector';
    }

    /** {@inheritdoc} */
    final public function getDesc()
    {
        // Отменить первую пустую строку для мультисписка
        $this->setDescVal('emptyStr', false);

        return parent::getDesc();
    }
}
