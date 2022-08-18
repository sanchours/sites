<?php

namespace skewer\base\section\params;

use skewer\base\section\models\ParamsAr;
use yii\base\InvalidConfigException;

/**
 * Валидатор для шаблонов
 * Class TemplateValidator.
 */
class TemplateValidator extends \yii\validators\Validator
{
    protected $parent = 0;

    protected function validateValue($value)
    {
        if (!$this->parent) {
            return;
        }

        if (!$value) {
            throw new InvalidConfigException('TemplateValidator: Template can not be empty!');
        }
        if ((int) $value <= 0) {
            throw new InvalidConfigException('TemplateValidator: Template not valid!');
        }
        if ($this->parent == $value) {
            throw new InvalidConfigException('TemplateValidator: You can not specify the template itself!');
        }
        $aChildren = \skewer\base\section\Parameters::getChildrenList($this->parent);
        if (!$aChildren) {
            $aChildren = [];
        }

        if (in_array($value, $aChildren)) {
            throw new InvalidConfigException('TemplateValidator: You can not specify a template of his heirs!');
        }
    }

    public function validateAttributes($model, $attributes = null)
    {
        if ($model instanceof ParamsAr) {
            $this->parent = $model->parent;
        }

        parent::validateAttributes($model, $attributes);
    }
}
