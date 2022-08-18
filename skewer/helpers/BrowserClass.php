<?php
declare(strict_types=1);

namespace skewer\helpers;

use Browser;
use skewer\components\sluggable\Inflector;

class BrowserClass
{
    const BROWSER_CLASS = 'browser';

    public static function get(): string
    {
        $browser = new Browser();
        $slug = Inflector::slug($browser->getBrowser());
        return self::BROWSER_CLASS . '-' . $slug;
    }
}
