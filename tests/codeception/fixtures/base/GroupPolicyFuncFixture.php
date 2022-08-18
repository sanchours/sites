<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class GroupPolicyFuncFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\auth\models\GroupPolicyFunc';

    public $dataFile = 'tests/codeception/fixtures/base/data/group_policy_func.php';
}
