<?php

namespace skewer\generators\view;

class HideView extends PrototypeView
{
    protected $sType = 'string hidden';

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return '<li hidden><?= $aNameField["' . $this->sName . '"]; ?> : <?= $' . $this->sName . '; ?></li>';
    }
}
