<?php

declare(strict_types=1);

namespace skewer\components\forms\components\handlerType;

use skewer\build\Page\Forms\FormEntity;
use skewer\components\forms\Api as FormApi;

/**
 * Class toMethod
 * Тип формы: отправка в метод.
 */
class toMethod extends Prototype
{
    public static $name = 'toMethod';

    public $message = 'send_to_method';

    const PARENT_CLASS_CUSTOM_FORM = 'skewer\components\forms\entities\BuilderEntity';
    const PARENT_CLASS_SERVICE_METHOD = 'skewer\base\site\ServicePrototype';

    /**
     * @param $value
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function validateHandler(string $value)
    {
        if (!empty($value)) {
            if ($this->checkClassForCustomForm($value) === false) {
                $this->checkValueAsClassAndMethod($value);
            }
        }
    }

    /**
     * @param FormEntity $formEntity
     * @param null|int $section
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return bool|mixed
     */
    public function saveData(FormEntity $formEntity, int $section = null)
    {
        return $this->send2Method($formEntity);
    }

    /**
     * Обработка результатов формы внутренним методом
     *
     * @param FormEntity $formEntity
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return bool|mixed
     */
    public function send2Method(FormEntity $formEntity)
    {
        $value = $formEntity->formAggregate->handler->value;
        if (!$value || FormApi::getChildClassEntity($formEntity->formAggregate)) {
            return false;
        }

        list($curObj, $methodName) = $this->checkValueAsClassAndMethod($value);

        $result = call_user_func_array([$curObj, $methodName], [$this]);

        return $result;
    }

    /**
     * Проверка значения на адекватность установки:
     * верные ли класс (зависим ли от skewer\base\site\ServicePrototype),
     * существует ли метод в этом классе.
     *
     * @param string $value
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return array
     */
    private function checkValueAsClassAndMethod(string $value): array
    {
        list($objectName, $methodName) = explode('.', $value);

        if (!isset($objectName) || !isset($methodName)) {
            throw new \Exception(\Yii::t('forms', 'wrong_format'));
        }

        $oCurClass = new \ReflectionClass($objectName);

        if (!($oCurClass instanceof \ReflectionClass)) {
            throw new \Exception(\Yii::t('forms', 'class_not_created'));
        }

        if ($oCurClass->getParentClass()->name !== self::PARENT_CLASS_SERVICE_METHOD) {
            throw new \Exception(\Yii::t('forms', 'wrong_class'));
        }

        $curObj = new $objectName();
        if (!method_exists($curObj, $methodName)) {
            throw new \Exception(\Yii::t('forms', 'wrong_method'));
        }

        return [$curObj, $methodName];
    }

    /**
     * Проверка значения на адекватность установки:.
     *
     * @param string $value
     *
     * @throws \Exception
     * @throws \ReflectionException
     *
     * @return bool
     */
    public function checkClassForCustomForm(string $value): bool
    {
        if (class_exists($value)) {
            $refClass = new \ReflectionClass($value);

            if (!($refClass instanceof \ReflectionClass)) {
                throw new \Exception(\Yii::t('forms', 'class_not_created'));
            }

            if ($refClass->getParentClass()->name !== self::PARENT_CLASS_CUSTOM_FORM) {
                if (!$this->checkClassForCustomForm($refClass->getParentClass()->name)) {
                    throw new \Exception(\Yii::t('forms', 'wrong_class'));
                }
            }

            return true;
        }

        return false;
    }
}
