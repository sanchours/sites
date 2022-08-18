<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class GroupPolicyDataFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\auth\models\GroupPolicyData';

    public $dataFile = 'tests/codeception/fixtures/base/data/group_policy_data.php';
}
