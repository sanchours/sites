<?php

declare(strict_types=1);

namespace skewer\components\preloadResources;

use Browser;
use skewer\base\section\Parameters;

class PreloadResources
{
    public static function getTagsLink(int $sectionId): string
    {
        $browser = new Browser();
        $fieldName = ($browser->isMobile() || $browser->isTablet())
            ? 'preloadResourcesMobile'
            : 'preloadResourcesDesktop';

        return Parameters::getShowValByName($sectionId, '.', $fieldName, true) ?: '';
    }
}