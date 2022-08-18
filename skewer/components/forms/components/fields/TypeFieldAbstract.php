<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\components\dto\FieldFormBuilderByType;
use skewer\components\forms\components\TypeObjectInterface;
use skewer\components\forms\service\TypeOfValidService;
use yii\base\UserException;

abstract class TypeFieldAbstract implements TypeObjectInterface
{
    public $hasExtraFile = false;

    /** @var bool Необходимо сохранять файл после его валидации */
    public $needSaveFile = false;

    /**
     * @var string
     * Тип параметра  для отображения в админке
     */
    protected $typeExtJs = 'string';

    /**
     * @var string
     * Тип параметра в базе
     */
    protected $typeDB = 'varchar';

    /** @var int $lengthValueDB Длинна ячейки */
    protected $lengthValueDB = 255;

    /** @var bool $isEditSizeDB Отсутствие размерности у объектов в БД */
    protected $isEditSizeDB = true;

    private $_serviceTypeOfValid;

    public function isEditSizeDB(): bool
    {
        return $this->isEditSizeDB;
    }

    public function getLengthValueDB(): int
    {
        return $this->lengthValueDB;
    }

    public function setLengthValueDB(int $lengthValue = null)
    {
        if ($this->isEditSizeDB() && (int)$lengthValue > 0) {
            $this->lengthValueDB = $lengthValue;
        }
    }

    public function getTypeExtJs(): string
    {
        return $this->typeExtJs;
    }

    /**
     * @return null|string
     */
    final public function getTypeDB()
    {
        return $this->typeDB;
    }

    public function getDefaultDisplayTypes(): int
    {
        return key($this->getDisplayTypes());
    }

    /**
     * типы валидации для текущего типа поля.
     *
     * @return array
     */
    protected function getAvailableTypesOfValid(): array
    {
        return $this->_serviceTypeOfValid->getPathsToObjects();
    }

    final public function getTypesOfValid(): array
    {
        return $this->_serviceTypeOfValid->getTypesOfValidByPath(
            $this->getAvailableTypesOfValid()
        );
    }

    final public function __construct()
    {
        $this->_serviceTypeOfValid = new TypeOfValidService();
    }

    /**
     * @param string $typeOfValid
     *
     * @throws UserException
     * @throws \ReflectionException
     *
     * @return bool
     */
    final public function hasTypeOfValid(string $typeOfValid): bool
    {
        if ($this->_serviceTypeOfValid->hasType($typeOfValid)) {
            $typeObject = $this->_serviceTypeOfValid->getTypeByName($typeOfValid);
            $className = is_object($typeObject) ? get_class($typeObject) : null;
            if (in_array($className, $this->getAvailableTypesOfValid())) {
                return true;
            }
        } else {
            throw new UserException(\Yii::t(
                'forms',
                'not_found_type_of_valid'
            ));
        }

        return false;
    }

    final public function getTitle(): string
    {
        return \Yii::t('forms', 'type_' . lcfirst($this->getName()));
    }

    final public function getName(): string
    {
        $paramsPath = explode('\\', get_class($this));

        return $paramsPath[count($paramsPath) - 1];
    }

    /**
     * Получение всех доступных типов отображения.
     *
     * @return array
     */
    final public function getDisplayTypes(): array
    {
        $sPath = RELEASEPATH . 'components/forms/templates/fields/' . $this->getName();

        if (file_exists($sPath)) {
            $aFileInDir = array_slice(scandir($sPath), 2);

            foreach ($aFileInDir as $iKey => $sValue) {
                $sValue = file_get_contents("{$sPath}/{$sValue}");

                $beginTitle = mb_strpos($sValue, '{#') + 2;
                $endTitle = mb_strpos($sValue, '#}');

                if ($endTitle && ($endTitle > $beginTitle)) {
                    $sName = mb_substr(
                        $sValue,
                        $beginTitle,
                        $endTitle - $beginTitle
                    );
                    $aResult[++$iKey] = \Yii::t('forms', trim($sName));
                } else {
                    $aResult[++$iKey] = 'Неверный формат названия!';
                }
            }
        }

        return $aResult ?? ['---'];
    }

    final public function hasDisplayType(int $displayType): bool
    {
        $types = $this->getDisplayTypes();

        return isset($types[$displayType]);
    }

    /**
     * Преобразование входящих дефолтных значений к тому виду,
     * который нужен для отображения в шаблоне.
     *
     * @param string $defaultValue
     * @param array $variants
     * @param array $presetValues
     *
     * @return array
     */
    public function parse(
        string $defaultValue,
        array $variants = [],
        array $presetValues = []
    ) {
        return [];
    }

    /**
     * Пропускаем обработку в списке заказов из форм
     *
     * @return bool
     */
    public function skipOnList()
    {
        return false;
    }

    public function deleteExtraData(int $idForm, string $slugForm, int $idOrder)
    {
    }

    /**
     * Очистка данных при смене типа поля с файла на иной.
     *
     * @param int $idForm
     * @param string $fieldName
     *
     * @return bool
     */
    public function clearExtraData(int $idForm, string $fieldName)
    {
        return false;
    }

    /**
     * Очистка данных при смене типа поля на файл.
     *
     * @param $formName
     * @param $fieldName
     */
    public function clearExtraDataOnExit($formName, $fieldName)
    {
    }

    public function deletePrivateFiles($sPath)
    {
    }

    public function setFieldValue(string $name, string $value): string
    {
        return strip_tags($value);
    }

    /**
     * @param string $value
     *
     * @return null|int|string
     */
    public function getValueForDB(string $value)
    {
        return $value;
    }

    /**
     * Построение интерфейся для модуля formOrder.
     *
     * @param FormBuilder $form
     * @param FieldFormBuilderByType $fieldFormBuilder
     */
    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        $form->field(
            $fieldFormBuilder->slug,
            $fieldFormBuilder->title,
            $this->typeExtJs
        );
    }

    /**
     * Получение форматированных значений для письма.
     *
     * @param string $sParamValue
     * @param string $sParamDefault
     *
     * @return mixed
     */
    public function getValueForLetter($sParamValue, $sParamDefault)
    {
        return $sParamValue;
    }

    /**
     * Заменить ключи на значения в полях с вариантами значений.
     *
     * @param string $defaultValue
     * @param string $value
     * @param array $defaultValues
     */
    public function changeDefaultValueForLetter(
        string &$defaultValue,
        string &$value,
        array $defaultValues
    ) {
    }

    /**
     * Получение дополнительных правил валидациии.
     *
     * @param int $maxLength
     *
     * @return array
     */
    public function getValidateRules(int $maxLength): array
    {
        return ['maxlength' => $maxLength];
    }

    /**
     * Получение контента для отправки файла в письме.
     *
     * @param $param_name
     *
     * @return string
     */
    public function getExtraData($param_name)
    {
        return '';
    }

    /**
     * Установка значений, в соответствии с переданными.
     *
     * @param $sValue
     * @param $sParamDef
     *
     * @return mixed
     */
    public function getTrueValue($sValue, $sParamDef)
    {
        return $sValue;
    }

    /**
     * @param string $title
     * @param string $value
     *
     * @return string
     */
    public function getParseData4CRM(string $title, string $value): string
    {
        return "{$title}: {$value}";
    }
}
