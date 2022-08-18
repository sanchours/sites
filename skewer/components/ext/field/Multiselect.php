<?php

namespace skewer\components\ext\field;

/**
 * Формирование контента для ComboBox-a с multiselect-ом
 * Class Multiselect.
 */
class Multiselect extends Select
{
    /** {@inheritdoc} */
    public function getView()
    {
        return 'multiselect';
    }

    /** {@inheritdoc} */
    final public function getDesc()
    {
        // Отменить первую пустую строку для мультисписка
        $this->setDescVal('emptyStr', false);

        return parent::getDesc();
    }
}
