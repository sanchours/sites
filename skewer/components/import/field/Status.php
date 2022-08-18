<?php

declare(strict_types=1);

namespace skewer\components\import\field;

class Status extends Prototype {

    const DELETED = 'удален';

    public function beforeExecute()
    {
        if ($this->getValue() === self::DELETED) {
            $this->getTask()->deleteCurrentGood();
        }

        parent::beforeExecute();
    }


    public function getValue()
    {
        return mb_strtolower(implode(',', $this->values), 'UTF-8');
    }
}