<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class PhotoGalleryAlbumsFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\gallery\models\Albums';

    public $dataFile = 'tests/codeception/fixtures/base/data/photogallery_albums.php';
}
