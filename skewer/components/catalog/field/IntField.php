<?php

namespace skewer\components\catalog\field;

class IntField extends StringField
{
    public function getInputMaskOptions()
    {
        return "'alias': 'integer', 'rightAlign': false";
    }
}
