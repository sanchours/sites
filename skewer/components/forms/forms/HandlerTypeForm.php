<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\build\Page\Forms\FormEntity;
use skewer\components\forms\components\handlerType\Prototype;
use skewer\components\forms\components\handlerType\toMethod;
use yii\base\UserException;

/**
 * Class HandlerTypeForm
 * отвечает за тип обработчика форм
 *
 * @property string $title
 * @property string $value
 * @property string $type
 */
class HandlerTypeForm extends InternalForm
{
    private $_type;
    private $_value;

    /** @var Prototype $typeObject */
    private $typeObject;

    const HANDLER_TO_BASE = 'toBase';
    const HANDLER_TO_MAIL = 'toMail';
    const HANDLER_TO_METHOD = 'toMethod';

    public function __construct(
        string $handlerType,
        string $handlerValue = '',
        array $config = []
    ) {
        $this->_type = $handlerType;
        $this->_value = $handlerValue;

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['type', 'value', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * Для отображения в админке адекватного названия типа обработчика.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return \Yii::t('forms', $this->_type);
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Получение списока типов обработчиков.
     *
     * @return array
     */
    public static function getHandlerTypes()
    {
        return [
            self::HANDLER_TO_BASE => \Yii::t('forms', self::HANDLER_TO_BASE),
            self::HANDLER_TO_MAIL => \Yii::t('forms', self::HANDLER_TO_MAIL),
            self::HANDLER_TO_METHOD => \Yii::t(
                'forms',
                self::HANDLER_TO_METHOD
            ),
        ];
    }

    /**
     * Настройка редактирования типа обработчика формы.
     *
     * @param bool $system
     *
     * @return bool
     */
    public function canNotEditType(bool $system)
    {
        if ($system && $this->_type === self::HANDLER_TO_METHOD) {
            $aParams = explode('.', $this->_value);
            /*Проверим похоже ли это не метод*/
            if (count($aParams) == '2' && class_exists($aParams[0])) {
                $oObject = new $aParams[0]();
                if (
                    method_exists($oObject, $aParams[1])
                    && get_parent_class($oObject) === toMethod::PARENT_CLASS_SERVICE_METHOD
                ) {
                    return true;
                }
            }

            if (class_exists($this->_value)) {
                return true;
            }
        }

        return false;
    }

    public function getShortNameObject(): string
    {
        return 'handler';
    }

    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param string $value
     *
     * @throws UserException
     */
    public function setValue(string $value)
    {
        $this->getTypeObject()->validateHandler($value);
        $this->_value = $value;
    }

    /**
     * @param string $type
     *
     * @throws UserException
     */
    public function setType(string $type)
    {
        $this->_type = $type;
        $this->setTypeObject();
    }

    public function getType()
    {
        return $this->_type;
    }

    /**
     * @throws UserException
     */
    public function setTypeObject()
    {
        $typeClass = Prototype::getNamespace() . '\\' . $this->_type;

        if (class_exists($typeClass)) {
            /**@param Prototype $oClassField */
            $this->typeObject = new $typeClass();
        } else {
            throw new UserException('Такого типа обработчика форм не существует');
        }
    }

    /**
     * @throws UserException
     *
     * @return mixed
     */
    public function getTypeObject(): Prototype
    {
        if (!$this->typeObject) {
            $this->setTypeObject();
        }

        return $this->typeObject;
    }

    public function isBaseType(): bool
    {
        return $this->type === self::HANDLER_TO_BASE;
    }

    public function isMailType(): bool
    {
        return $this->type === self::HANDLER_TO_MAIL;
    }

    public function isMethodType(): bool
    {
        return $this->type === self::HANDLER_TO_METHOD;
    }

    /**
     * @param FormEntity $formEntity
     * @param int $idSection
     *
     * @throws UserException
     *
     * @return bool
     */
    public function saveData(FormEntity $formEntity, int $idSection): bool
    {
        return $this->getTypeObject()->saveData($formEntity, $idSection);
    }
}
