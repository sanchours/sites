<?php

namespace skewer\build\Page\CatalogViewer;

use skewer\base\site\RobotsInterface;

class Robots implements RobotsInterface
{
    public static function getRobotsDisallowPatterns()
    {
        return [
            '/*view=',
            '/*sort=',
           // '/*?page='
        ];
    }

    public static function getRobotsAllowPatterns()
    {
        return false;
    }
}
