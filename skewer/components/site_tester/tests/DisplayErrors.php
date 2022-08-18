<?php

namespace skewer\components\site_tester\Tests;

use skewer\components\site_tester\Api;
use skewer\components\site_tester\TestPrototype;

class DisplayErrors extends TestPrototype
{
    public static $name = 'Checking PHP error output';

    public $indexFiles = [
        'web/index.php',
    ];

    const regEx = '(ini_set\(\'display_errors\',\s*\S{1,10})';

    const ON = 1;
    const OFF = 0;

    public $labels = [
        0 => 'enabled',
        1 => 'disabled',
    ];

    public function execute()
    {
        $status = (Api::getSiteMode() == 'prod') ? static::OFF : static::ON;
        $this->setStatusOk();

        foreach ($this->indexFiles as $file) {
            $match = [];
            $find = preg_match(static::regEx, file_get_contents(ROOTPATH . $file), $match);
            if ($find) {
                foreach ($match as $row) {
                    if ($this->getData($row) != $status) {
                        $this->setStatusWarning('[' . $file . '] Errors are ' . $this->labels[$status]);
                    } else {
                        $this->addMessageInfo('[' . $file . '] Errors are ' . $this->labels[!$status]);
                    }
                }
            }
        }
    }

    public function getData($str)
    {
        $str = mb_substr($str, 0, mb_strlen($str) - 2);
        $var = explode(',', $str);
        $flag = trim($var[1]);

        if ($flag == 'false') {
            return 0;
        }
        if ($flag == 'true') {
            return 1;
        }

        return (int) $flag;
    }
}
