<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class PhotoGalleryProfilesFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\gallery\models\Profiles';

    public $dataFile = 'tests/codeception/fixtures/base/data/photogallery_profiles.php';
}
