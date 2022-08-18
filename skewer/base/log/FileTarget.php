<?php

namespace skewer\base\log;

use yii\helpers\ArrayHelper;

class FileTarget extends \yii\log\FileTarget
{
    public function formatMessage($message)
    {
        $item = ArrayHelper::getValue($message, 0, '');

        switch (gettype($item)) {
            case 'boolean':
                return '[' . date('r') . '](bool): ' . ($item ? 'TRUE' : 'FALSE');
            case 'integer':
                return '[' . date('r') . "](int): {$item}";
            case 'double':
                return '[' . date('r') . "](double): {$item}";
            case 'string':
                return '[' . date('r') . "](str): {$item}";
            case 'array':
                return '[' . date('r') . '](array): ' . print_r($item, true);
            case 'object':
                return '[' . date('r') . '](obj): ' . print_r($item, true);
            case 'resource':
                return '[' . date('r') . '](res): ' . print_r($item, true);
            case 'NULL':
                return '[' . date('r') . '](null): NULL';
            default:
                return '[' . date('r') . '](unknown): ' . print_r($item, true);
        }
    }
}
