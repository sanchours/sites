<?php

namespace skewer\components\site_tester\Tests;

use skewer\components\site_tester\TestPrototype;

class Chmod extends TestPrototype
{
    const ALL = 755;

    public static $name = 'Checking the rights to files and folders';

    public $rules = [
        'backup',
        'files',
        'sitemap.xml',
        'sitemap_files',
        'robots_files',
        'robots.txt',
        'log',
    ];

    public $no_check = [
        '.',
        '..',
        '.git',
        '.idea',
        'vendor',
    ];

    public function createRules()
    {
        foreach ($this->rules as &$rule) {
            $rule = ROOTPATH . $rule;
        }
    }

    public function execute()
    {
        $this->createRules();
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
            if (!$this->checkFile($dir . $file)) {
                $this->setStatusError('[bad permission] ' . $dir . $file);
            }
            if (is_dir($dir . $file)) {
                $this->checkDir($dir . $file . \DIRECTORY_SEPARATOR);
            }
        }
    }

    public function checkFile($file)
    {
        $perm = (int) mb_substr(decoct(fileperms($file)), -3, 3);

        foreach ($this->rules as $rule) {
            $pos = mb_strpos($rule, $file);

            if ($pos === false) {
                if ($perm != static::ALL) {
                    return false;
                }
            } else {
                if ($perm != 777) {
                    return false;
                }
            }
        }

        return true;
    }
}
