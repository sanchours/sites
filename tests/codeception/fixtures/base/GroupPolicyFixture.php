<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class GroupPolicyFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\auth\models\GroupPolicy';

    public $dataFile = 'tests/codeception/fixtures/base/data/group_policy.php';
}
