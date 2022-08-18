<?php

namespace skewer\components\site_tester\Tests;

use skewer\components\seo\Robots as RobotsSEO;
use skewer\components\site_tester\Api;
use skewer\components\site_tester\TestPrototype;

class Robots extends TestPrototype
{
    public static $name = 'Checking robots.txt file';

    public $disallowRules = [
        'dev' => [
            '/',
        ],
        'prod' => [
            '/search/',
            '/admin/',
            '*/?objectId',
            '/files/rss/feed.rss',
            '*/files/rss/feed.rss/',
        ],
    ];

    public function execute()
    {
        $mode = Api::getSiteMode();

        $pathForMainRobots = RobotsSEO::getFullFilePath();

        if (file_exists($pathForMainRobots)) {
            $handle = fopen($pathForMainRobots, 'r');
            if ($handle) {
                while (($row = fgets($handle, 4096)) !== false) {
                    $rule = $this->getRule($row);
                    if ($rule) {
                        if (in_array($rule, $this->disallowRules[$mode])) {
                            $this->setStatusOk('[OK] Disallow: ' . $rule);
                        } else {
                            $this->setStatusError('[ERROR] Disallow: ' . $rule);
                        }
                    }
                }
            } else {
                $this->setStatusFail('robots.txt file does not exist');
            }
        } else {
            $this->setStatusFail('robots.txt file does not exist');
        }
    }

    private function getRule($str)
    {
        $row = explode(':', $str);

        return ($row[0] == 'Disallow') ? trim($row[1]) : false;
    }
}
