<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\components\fields\TypeFieldAbstract;
use skewer\components\forms\components\typesOfValid\TypeOfValidAbstract;
use skewer\components\forms\service\TypeFieldService;
use skewer\components\forms\service\TypeOfValidService;
use yii\base\UserException;

/**
 * Class TypeFieldForm
 * Базовыми параметрами являются безопасные параметры класса.
 *
 * @property $name
 * @property $typeOfValid
 * @property $maxLength
 * @property $displayType
 * @property $warning
 * @property $default
 * @property $title
 */
class TypeFieldForm extends InternalForm
{
    const DEFAULT_NAME_TYPE = 'Input';
    const DEFAULT_TYPE_VALID = 'Text';
    const TYPE_VALID_PHONE = 'Tel';
    const TYPE_VALID_FILE = 'File';

    private $_name = self::DEFAULT_NAME_TYPE;
    private $_typeOfValid = self::DEFAULT_TYPE_VALID;
    private $_maxLength = 255;
    private $_displayType = 0;
    private $_default = '';

    /** @var TypeFieldAbstract $_fieldObject */
    private $_fieldObject;

    /** @var TypeOfValidAbstract $_typeOfValidObject */
    private $_typeOfValidObject;

    /** @var TypeFieldService $_serviceTypeField */
    private $_serviceTypeField;

    private $_warning = [];

    /**
     * TypeFieldForm constructor.
     *
     * @param string $type
     * @param string $typeValid
     * @param array $config
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function __construct(
        $type = self::DEFAULT_NAME_TYPE,
        $typeValid = null,
        array $config = []
    ) {
        $this->setName($type);
        if ($typeValid == null) {
            $typeValid = $this->getDefaultTypeValid();
        }
        $this->setTypeOfValid($typeValid);

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['name', 'displayType'], 'required'],
            [['maxLength', 'displayType'], 'integer'],
            [['name', 'typeOfValid'], 'string', 'max' => 255],
            [['title', 'default'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('forms', 'param_type'),
            'typeOfValid' => \Yii::t('forms', 'param_validation_type'),
        ];
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getTitle(): string
    {
        return $this->_fieldObject->getTitle();
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getDefault()
    {
        return $this->_default;
    }

    public function setDefault(string $default = null)
    {
        $this->_default = $default;
    }

    /**
     * @return null|string
     */
    public function getTypeOfValid()
    {
        return $this->_typeOfValid;
    }

    /**
     * Получение типа валидации для отображения в шаблоне.
     *
     * @return string
     */
    public function getTypeOfValidForTemplate()
    {
        return mb_strtolower($this->_typeOfValid);
    }

    public function getMaxLength(): int
    {
        return $this->_maxLength;
    }

    /**
     * Получение типа объекта.
     *
     * @return TypeFieldAbstract
     */
    public function getFieldObject(): TypeFieldAbstract
    {
        return $this->_fieldObject;
    }

    /**
     * Получение типа объекта для валидации.
     *
     * @throws \ReflectionException
     *
     * @return TypeOfValidAbstract
     */
    public function getTypeOfValidObject(): TypeOfValidAbstract
    {
        if (!$this->_typeOfValidObject) {
            $this->setTypeOfValidObject();
        }

        return $this->_typeOfValidObject;
    }

    /**
     * @throws \ReflectionException
     */
    private function setTypeOfValidObject()
    {
        $serviceTypeOfValid = new TypeOfValidService();
        $this->_typeOfValidObject = $serviceTypeOfValid->getTypeByName(
            $this->_typeOfValid
        );
        assert($this->_typeOfValidObject instanceof TypeOfValidAbstract);
    }

    public function getDisplayType(): int
    {
        return $this->_displayType;
    }

    /**
     * @param string $name
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function setName(string $name)
    {
        $this->_serviceTypeField = new TypeFieldService();

        if (!$this->_serviceTypeField->hasType($name)) {
            throw new UserException(\Yii::t('forms', 'not_found_type'));
        }

        $this->_name = $name;

        $this->_fieldObject = $this->_serviceTypeField->getTypeByName($name);
        $this->setMaxLength();
        $this->setDisplayType();
    }

    public function setMaxLength(int $maxLength = null)
    {
        $this->_fieldObject->setLengthValueDB($maxLength);
        $this->_maxLength = $this->_fieldObject->getLengthValueDB();
    }

    /**
     * @param null|int $displayType
     */
    public function setDisplayType(int $displayType = null)
    {
        if ($displayType !== null && $this->_fieldObject->hasDisplayType($displayType)) {
            $this->_displayType = $displayType;
        } else {
            $this->_displayType = $this->_fieldObject->getDefaultDisplayTypes();
        }
    }

    /**
     * Удаление дополнительных данных поля.
     *
     * @param int $idForm
     * @param int $idField
     */
    public function deleteExtraData(int $idForm, int $idField)
    {
        $path = "uploads/{$idForm}/{$idField}/";
        $this->_fieldObject->deletePrivateFiles($path);
    }

    /**
     * @param $typeOfValid
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function setTypeOfValid(string $typeOfValid)
    {
        if (!$typeOfValid) {
            $typeOfValid = $this->getDefaultTypeValid();
        }

        if (!$this->_fieldObject->hasTypeOfValid($typeOfValid)) {
            throw new UserException(
                \Yii::t('forms', 'not_permitted_type_valid')
            );
        }

        $this->_typeOfValid = $typeOfValid;
    }

    /**
     * @throws \ReflectionException
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->_serviceTypeField->getTypesWithTitle();
    }

    /**
     * @return array
     */
    public function getTypesValid(): array
    {
        return $this->_fieldObject->getTypesOfValid();
    }

    /**
     * Установка ожидания.
     *
     * @param $title
     * @param $message
     */
    public function setWarning($title, $message)
    {
        $this->_warning = [
            'title' => $title,
            'message' => $message,
        ];
    }

    public function getWarning(): array
    {
        return $this->_warning;
    }

    /**
     * Проверка необходимости валидации как телефона.
     *
     * @return bool
     */
    public function isPhoneValidate(): bool
    {
        return $this->typeOfValid === self::TYPE_VALID_PHONE;
    }

    public function isFile(): bool
    {
        return $this->_name === self::TYPE_VALID_FILE;
    }

    public function getShortNameObject(): string
    {
        return 'type';
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function needAddRuleInValidation(): bool
    {
        return $this->getTypeOfValidObject()->needAddRuleInValidation();
    }

    public function getDefaultTypeValid(): string
    {
        $typesOfValidationKeys = array_keys($this->getFieldObject()->getTypesOfValid());

        return count($typesOfValidationKeys) !== 0 && in_array(self::DEFAULT_TYPE_VALID, $typesOfValidationKeys)
            ? self::DEFAULT_TYPE_VALID
            : array_shift($typesOfValidationKeys);
    }

}
