<?php

namespace skewer\build\Page\CategoryViewer\templates\food;

use skewer\build\Page\CategoryViewer\AssetPrototype;

class Asset extends AssetPrototype
{
    public $sourcePath = '@skewer/build/Page/CategoryViewer/templates/food/web/';

    public $depends = [
        'skewer\build\Page\CategoryViewer\Asset',
    ];
}
