<?php

namespace skewer\build\Page\CategoryViewer\templates\clothing;

use skewer\build\Page\CategoryViewer\AssetPrototype;

class Asset extends AssetPrototype
{
    public $sourcePath = '@skewer/build/Page/CategoryViewer/templates/clothing/web/';

    public $depends = [
        'skewer\build\Page\CategoryViewer\Asset',
    ];
}
