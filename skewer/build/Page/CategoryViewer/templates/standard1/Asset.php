<?php

namespace skewer\build\Page\CategoryViewer\templates\standard1;

use skewer\build\Page\CategoryViewer\AssetPrototype;

class Asset extends AssetPrototype
{
    public $sourcePath = '@skewer/build/Page/CategoryViewer/templates/standard1/web/';

    // Удалить после того как файл category-viewer.css будет измененен на новую логику
    public $depends = [
        'skewer\build\Page\CategoryViewer\Asset',
    ];
}
