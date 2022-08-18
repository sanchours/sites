<?php

namespace skewer\components\catalog\field;

class File extends Prototype
{
    protected function build($value, $rowId, $aParams)
    {
        $fileName = mb_substr($value, mb_strrpos($value, '/') + 1);
        $fileLink = $value;
        $out = $value ? '<a href="' . $fileLink . '">' . $fileName . '</a>' : '';

        $html = ($out) ? $this->getHtmlData($out) : '';

        return [
            'file_name' => $fileName,
            'file_link' => $fileLink,
            'value' => $value,
            'tab' => $out,
            'html' => $html,
        ];
    }
}
