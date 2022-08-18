<?php

namespace skewer\build\Catalog\Dictionary;

use skewer\components\config\InstallPrototype;
use skewer\components\gallery\Profile;

class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        //профиль для основной галереи
        $iProfileIdGallery = Profile::setProfile([
            'type' => Profile::TYPE_DICT,
            'alias' => 'image_dict',
            'title' => \Yii::t('data/gallery', 'profile_dict_name', [], \Yii::$app->language),
            'active' => 1,
        ]);

        if (!$iProfileIdGallery) {
            $this->fail('not successful attempt to add gallery profile');
        }

        return true;
    }

    // func

    public function uninstall()
    {
        $aProfileGallery = Profile::getByAlias('image_dict');
        if ($aProfileGallery) {
            Profile::removeProfile($aProfileGallery['id']);
        }

        return true;
    }

    // func
}
