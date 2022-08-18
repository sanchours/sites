<?php

namespace skewer\build\Page\Gallery;

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
        // добавляем новый профиль форматов
        $profile_id = Profile::setProfile([
            'type' => Profile::TYPE_SECTION,
            'alias' => 'section',
            'title' => \Yii::t('data/gallery', 'profile_section_name', [], \Yii::$app->language),
            'active' => 1,
        ]);

        if (!$profile_id) {
            $this->fail('cant create gallery profile');
        }

        return true;
    }

    // func

    public function uninstall()
    {
        return true;
    }

    // func
}// class
