<?php

namespace skewer\build\Tool\Dictionary\view;

use skewer\components\ext\view\FormView;

class UpdFieldLinkId extends FormView
{
    public $aProfiles;
    public $bIsNotLinked;
    public $iTypeId;
    public $sTitleLinkId;

    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $this->_form
            ->fieldSelect('link_id', $this->sTitleLinkId, $this->aProfiles, ['hidden' => $this->bIsNotLinked], false)
            ->setValue([
                'link_id' => $this->iTypeId,
            ]);
    }
}
