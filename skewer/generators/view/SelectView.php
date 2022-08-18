<?php

namespace skewer\generators\view;

class SelectView extends PrototypeView
{
    protected $sType = 'string';

    public function getCodeDetail()
    {
        $mCardId = $this->aField['link_id'];
        if ($mCardId) {
            return str_replace('%s', $this->sName, "// Обработка поля справочника - %s \n" .
                '       $aDictSelect = Dict::getValues( $this->' . $this->aField['name'] . ' ,$aFieldDict[\'%s\'],true);' . "\n" .
                '       $aFieldDict[\'%s\'] = (isset($aDictSelect[\'title\']))?$aDictSelect[\'title\']:\'\';');
        }
    }

    public function getProperties()
    {
        return [
            'private $' . $this->aField['name'] . ' = ' . $this->aField['link_id'] . ';',
        ];
    }
}
