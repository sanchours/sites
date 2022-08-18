<?php

namespace tests\codeception\fixtures\base;

use tests\codeception\fixtures\ActiveFixturePrototype;

class GroupPolicyModuleFixture extends ActiveFixturePrototype
{
    public $modelClass = '\skewer\components\auth\models\GroupPolicyModule';

    public $dataFile = 'tests/codeception/fixtures/base/data/group_policy_module.php';
}
