<?php

namespace tests\codeception\fixtures\base;

use skewer\components\config\ConfigUpdater;
use tests\codeception\fixtures\ActiveFixturePrototype;

class RegistryStorageFixture extends ActiveFixturePrototype
{
    public $tableName = 'registry_storage';

    public $dataFile = 'tests/codeception/fixtures/base/data/registry_storage.php';

    public function load()
    {
        parent::load();

        ConfigUpdater::init();
        ConfigUpdater::buildRegistry()->reloadData();
    }
}
