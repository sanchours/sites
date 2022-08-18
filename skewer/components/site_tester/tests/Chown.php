<?php

namespace skewer\components\site_tester\Tests;

use skewer\components\site_tester\TestPrototype;

class Chown extends TestPrototype
{
    const APACHE = 'apache';

    public static $name = 'Checking the owners of files and folders';

    public $no_check = [
        '.',
        '..',
        '.git',
        '.idea',
        'vendor',
    ];

    public function execute()
    {
        $this->setStatusOk();

        $this->checkDir(ROOTPATH);
    }

    public function checkDir($dir)
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if (in_array($file, $this->no_check)) {
                continue;
            }
            $this->checkFile($dir . $file);
            if (is_dir($dir . $file)) {
                $this->checkDir($dir . $file . \DIRECTORY_SEPARATOR);
            }
        }
    }

    public function checkFile($file)
    {
        $owner = posix_getpwuid(fileowner($file));
        if ($owner['name'] == static::APACHE) {
            $this->setStatusError('[' . $owner['name'] . "] Owner '" . $file . "'");
        }
    }
}
