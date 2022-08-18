<?php

use skewer\components\config\PatchPrototype;
use skewer\base\section\Parameters;

class Patch85495 extends PatchPrototype
{

    public $sDescription = 'Смена копирайта веб-канапе';

    public $bUpdateCache = false;

    public function execute()
    {
        Parameters::setParams(
            \Yii::$app->sections->root(),
            'copyright_dev',
            'source',
            null,
            '<p class="b-copy"><a href="https://www.web-canape.ru/razrabotka-sajta/?utm_source=copyright">Разработка</a> и <a href="https://www.web-canape.ru/prodvizhenie-sajtov/?utm_source=copyright">маркетинг</a> - WebCanape</p>'
        );
    }

}
