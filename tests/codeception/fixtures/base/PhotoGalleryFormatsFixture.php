<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class PhotoGalleryFormatsFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\gallery\models\Formats';

    public $dataFile = 'tests/codeception/fixtures/base/data/photogallery_formats.php';
}
