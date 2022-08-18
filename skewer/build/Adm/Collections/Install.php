<?php

namespace skewer\build\Adm\Collections;

use skewer\base\section\Parameters;
use skewer\base\site\Type;
use skewer\components\config\InstallPrototype;
use skewer\components\i18n\Languages;
use yii\base\UserException;

/**
 * Class Install.
 */
class Install extends InstallPrototype
{
    const GROUP_NAME = 'collections_adm';

    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        if (!Type::hasCatalogModule()) {
            throw new UserException('Catalog is not installed');
        }
        /** Массив имён всех используемых языков на сайте */
        $aLangs = Languages::getAllActiveNames();

        // Добавить модуль администрирования колекций на главной на все главные страницы сайта
        foreach ($aLangs as $sLang) {
            $iSectionId = \Yii::$app->sections->main($sLang);

            if (!Parameters::getByName($iSectionId, self::GROUP_NAME, Parameters::objectAdm, true)) {
                Parameters::createParam([
                    'parent' => $iSectionId,
                    'group' => self::GROUP_NAME,
                    'name' => Parameters::objectAdm,
                    'value' => 'Collections',
                ])->save();
            }
        }

        return true;
    }

    // func

    public function uninstall()
    {
        Parameters::removeByGroup(self::GROUP_NAME);

        return true;
    }

    // func
}//class
