<?php

use skewer\build\Page\Profile\EditProfileEntity;
use skewer\components\config\PatchPrototype;
use skewer\components\forms\entities\FieldEntity;
use skewer\components\forms\entities\FormEntity;

class Patch78262 extends PatchPrototype
{
    public $sDescription = 'ЛК. Контактные данные в профиле клиента. Маска телефона.';

    public $bUpdateCache = false;

    public function execute()
    {
        $formProfile = FormEntity::getBySlug(EditProfileEntity::tableName());
        if ($formProfile instanceof FormEntity) {
            $phone = FieldEntity::find()
                ->select(['spec_style'])
                ->where(
                    [
                        'form_id' => $formProfile->id,
                        'slug' => 'phone',
                    ]
                )->one();

            if ($phone instanceof FieldEntity && $phone->spec_style === '') {
                FieldEntity::updateAll(
                    ['spec_style' => 'data-mask="phone"'],
                    [
                        'form_id' => $formProfile->id,
                        'slug' => 'phone',
                    ]
                );
            }
        }
    }
}
