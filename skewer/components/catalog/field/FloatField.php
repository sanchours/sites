<?php

namespace skewer\components\catalog\field;

class FloatField extends StringField
{
    public function getInputMaskOptions()
    {
        return "'alias': 'decimal', 'rightAlign': false";
    }
}
