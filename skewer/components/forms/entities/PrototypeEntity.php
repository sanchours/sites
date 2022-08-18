<?php

namespace skewer\components\forms\entities;

use skewer\components\sluggable\Inflector;
use yii\db\ActiveRecord;

/**
 * Class PrototypeEntity
 * отвечает за установку и получение аттрибутов из агрегаторов
 *  в соответствии с перекрытыми параметрами в методе InternalFormsAggregate->getInternalForms()
 * для каждой отдельной подформы агрегатора.
 */
class PrototypeEntity extends ActiveRecord
{
    public static function getById(int $id)
    {
        return self::findOne(['id' => $id]);
    }

    /**
     * Подстановка в нужные атрибуты необходимых значений.
     *
     * @param array $values
     * @param bool $safeOnly
     * @param array $attributesReplace
     */
    public function setAttributes($values, $safeOnly = true, $attributesReplace = [])
    {
        foreach ($values as $key => $value) {
            if (isset($attributesReplace[$key])) {
                $values[$attributesReplace[$key]] = $value;
                unset($values[$key]);
            }
        }

        parent::setAttributes($values, $safeOnly);
    }

    /**
     * Преобразование атрибутов entities в camelCase нотацию.
     *
     * @param null $names
     * @param array $except
     *
     * @return array
     */
    public function getAttributes($names = null, $except = [])
    {
        $values = [];
        if ($names === null) {
            $names = $this->attributes();
        }
        foreach ($names as $name) {
            $nameNewAttr = lcfirst(Inflector::camelize($name));
            $values[$nameNewAttr] = $this->{$name};
        }
        foreach ($except as $name) {
            unset($values[$name]);
        }

        return $values;
    }
}
