<?php

declare(strict_types=1);

namespace unit\components\forms\forms;

use skewer\components\forms\components\fields\TypeFieldAbstract;
use skewer\components\forms\forms\TypeFieldForm;
use skewer\components\forms\service\TypeFieldService;
use yii\base\UserException;

class TypeFieldFormTest extends \PHPUnit\Framework\TestCase
{
    /** @var TypeFieldService $serviceTypeField */
    private $serviceTypeField;

    private $newDisplayType = 5;

    protected function setUp()
    {
        $this->serviceTypeField = new TypeFieldService();
    }

    protected function tearDown()
    {
        $this->serviceTypeField = null;
    }

    /**
     * Провайдер для проверка создания сущности по наименованию типа.
     *
     * @throws \ReflectionException
     *
     * @return \Generator
     */
    public function providerType()
    {
        $serviceTypeField = new TypeFieldService();

        /** @var TypeFieldAbstract $object */
        foreach ($serviceTypeField->getObjectTypes() as $object) {
            yield [$object->getName()];
        }
    }

    /**
     * @dataProvider providerType
     *
     * @param $inputName
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function test__construct(string $inputName)
    {
        $type = new TypeFieldForm($inputName);

        $this->setCompositeName($inputName);

        $this->assertEquals($type->getName(), $inputName);
        $this->assertNotNull($type->getTypeOfValid());

        $typeInner = $this->serviceTypeField->getTypeByName($inputName);

        $this->assertEquals($type->getMaxLength(), $typeInner->getLengthValueDB());
        $this->assertEquals(
            $type->getDisplayType(),
            $typeInner->getDefaultDisplayTypes()
        );
    }

    /**
     * @dataProvider providerType
     *
     * @param $inputName
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function testGetFieldObject(string $inputName)
    {
        $type = new TypeFieldForm($inputName);

        $this->setCompositeName($inputName);

        $typeInner = $this->serviceTypeField->getTypeByName($inputName);

        $this->assertEquals($type->getFieldObject(), $typeInner);
    }

    /**
     * Провайдер для проверка выводов ошибок для сущности.
     *
     * @return array
     */
    public function providerException()
    {
        return [
            ['', '', \Yii::t('forms', 'not_found_type')],
            ['test', '', \Yii::t('forms', 'not_found_type')],
            ['Input', 'Text', ''],
            ['Input', 'Test', \Yii::t('forms', 'not_found_type_of_valid')],
            ['Delimiter', 'Digits', \Yii::t('forms', 'not_permitted_type_valid')],
        ];
    }

    /**
     * @dataProvider providerException
     *
     * @param $inputName
     * @param $inputTypeValid
     * @param $messageExcept
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function testException(string $inputName, string $inputTypeValid, string $messageExcept)
    {
        if ($messageExcept) {
            $this->expectException(UserException::class);
            $this->expectExceptionMessage($messageExcept);
        }

        new TypeFieldForm($inputName, $inputTypeValid);
    }

    /**
     * Провайдер для проверки установки длинны поля в БД.
     *
     * @throws \ReflectionException
     *
     * @return \Generator
     */
    public function providerMaxLength()
    {
        $serviceTypeField = new TypeFieldService();

        /** @var TypeFieldAbstract $object */
        foreach ($serviceTypeField->getObjectTypes() as $object) {
            yield [$object->getName(), $object->getLengthValueDB(), $object->isEditSizeDB()];
        }
    }

    /**
     * @dataProvider providerMaxLength
     *
     * @param $name
     * @param $basicLength
     * @param $isEditLength
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function testSetMaxLength(string $name, int $basicLength, bool $isEditLength)
    {
        $maxLength = 30;

        $type = new TypeFieldForm($name);
        $type->setMaxLength($maxLength);

        $this->setCompositeName($name);

        $realLength = $isEditLength ? $maxLength : $basicLength;

        $this->assertEquals($type->getMaxLength(), $realLength);
    }

    /**
     * Провайдер для проверки установки типа показа поля в БД.
     *
     * @throws \ReflectionException
     *
     * @return \Generator
     */
    public function providerDisplayType()
    {
        $serviceTypeField = new TypeFieldService();

        /** @var TypeFieldAbstract $object */
        foreach ($serviceTypeField->getObjectTypes() as $object) {
            $realValue = $object->hasDisplayType($this->newDisplayType)
                ? $this->newDisplayType
                : $object->getDefaultDisplayTypes();
            yield [$object->getName(), $realValue];
        }
    }

    /**
     * @dataProvider providerDisplayType
     *
     * @param $name
     * @param $realValue
     *
     * @throws UserException
     * @throws \ReflectionException
     */
    public function testSetDisplayType(string $name, int $realValue)
    {
        $type = new TypeFieldForm($name);
        $type->setDisplayType($this->newDisplayType);

        $this->setCompositeName($name);

        $this->assertEquals($type->getDisplayType(), $realValue);
    }

    private function setCompositeName(string $nameClass)
    {
        $this->setName($this->getName() . " Class - {$nameClass}");
    }
}
