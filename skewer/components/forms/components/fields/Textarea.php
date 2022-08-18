<?php

namespace skewer\components\forms\components\fields;

class Textarea extends TypeFieldAbstract
{
    protected $typeExtJs = 'text';
    protected $typeDB = 'text';
    protected $lengthValueDB = 255;
}
