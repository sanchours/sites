<?php

namespace skewer\build\Page\CategoryViewer\templates\gadgets;

use skewer\build\Page\CategoryViewer\AssetPrototype;

class Asset extends AssetPrototype
{
    public $sourcePath = '@skewer/build/Page/CategoryViewer/templates/gadgets/web/';

    public $depends = [
        'skewer\build\Page\CategoryViewer\Asset',
    ];
}
