<?php

use skewer\build\Page\Search\SearchEntity;
use skewer\components\config\PatchPrototype;
use skewer\components\forms\entities\FormEntity;

class Patch84650 extends PatchPrototype
{
    public $sDescription = 'Снятие лицензионного соглашения у формы поиска';

    public $bUpdateCache = false;

    public function execute()
    {
        $entity = FormEntity::find()
            ->where(['slug' => SearchEntity::tableName()])
            ->one();

        assert($entity instanceof FormEntity);

        $entity->agree = 0;
        $entity->save();
    }
}
