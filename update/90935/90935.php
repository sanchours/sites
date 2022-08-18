<?php

use skewer\components\config\PatchPrototype;
use skewer\components\i18n\models\LanguageValues;

class Patch90935 extends PatchPrototype
{

    public $sDescription = 'Обновление ссылки на английский хелпер';

    public $bUpdateCache = false;

    public function execute()
    {
        LanguageValues::updateAll(
            ['value' => 'http://help.web-canape.ru/'],
            [
                'category' => 'adm',
                'language' => 'en',
                'message' => 'link_help'
            ]
        );
    }

}