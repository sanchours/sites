<?php

namespace skewer\components\ext\field;

/**
 * Класс для редактирования поля типа "ДатаВремя".
 */
class Datetime extends Prototype
{
    public function getView()
    {
        return 'datetime';
    }

    /** {@inheritdoc} */
    public function getValueList()
    {
        // перевести в объект "дата"
        $oDate = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getValue());

        // собрать строку по заданному формату
        return  $oDate ? $oDate->format('d.m.Y H:i') : '';
    }
}
