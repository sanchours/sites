<?php

namespace skewer\components\catalog\field;

class PaymentObject extends Prototype
{
    protected function build($value, $rowId, $aParams)
    {
        return [
            'value' => $value,
            'tab' => $value,
            'html' => $value,
        ];
    }
}
