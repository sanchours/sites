<?php

declare(strict_types=1);

namespace skewer\components\forms\entities\queries;

use skewer\components\forms\forms\HandlerTypeForm;
use yii\db\ActiveQuery;

class FieldQuery extends ActiveQuery
{
    public function onlyBaseType()
    {
        return $this->where(['handler_type' => HandlerTypeForm::HANDLER_TO_BASE]);
    }
}
