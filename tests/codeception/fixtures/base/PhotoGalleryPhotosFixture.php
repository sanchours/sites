<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class PhotoGalleryPhotosFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\gallery\models\Photos';

    public $dataFile = 'tests/codeception/fixtures/base/data/photogallery_photos.php';
}
