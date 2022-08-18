<?php

namespace skewer\build\Page\CategoryViewer\templates\materials;

use skewer\build\Page\CategoryViewer\AssetPrototype;

class Asset extends AssetPrototype
{
    public $sourcePath = '@skewer/build/Page/CategoryViewer/templates/materials/web/';
    public $depends = [
        'skewer\build\Page\CategoryViewer\Asset',
    ];
}
