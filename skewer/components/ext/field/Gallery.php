<?php

namespace skewer\components\ext\field;

use skewer\components\gallery\Profile;

/**
 * Редактор галерея.
 */
class Gallery extends Prototype
{
    public function getView()
    {
        return 'gallery';
    }

    /** {@inheritdoc} */
    public function getDesc()
    {
        if ($mShowVal = $this->getDescVal('show_val')) {
            $this->setProfileId($mShowVal);
        }

        return parent::getDesc();
    }

    /**
     * Установить id профиля галереи для поля.
     *
     * @param int|string $mProfile Id/Alias Профиля галереи
     */
    private function setProfileId($mProfile)
    {
        if (!$iProfileId = (int) $mProfile) {
            $iProfileId = ($aProfile = Profile::getByAlias($mProfile)) ? $aProfile['id'] : 0;
        }

        if ($iProfileId) {
            $this->setDescVal('gal_profile_id', $iProfileId);
        }
    }
}
