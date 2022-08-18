<?php

declare(strict_types=1);

namespace unit\components\forms\forms;

use skewer\components\forms\components\LabelPosition;
use skewer\components\forms\components\protection\BlockJs;
use skewer\components\forms\components\protection\HiddenField;
use skewer\components\forms\forms\SettingsFieldForm;
use yii\base\UserException;

class SettingsFieldFormTest extends \PHPUnit\Framework\TestCase
{
    public function providerConstruct(): array
    {
        return [
            [
                [
                    'title' => 'New Field',
                    'widthFactor' => 2,
                    'labelPosition' => LabelPosition::LABEL_POSITION_TOP,
                    'newLine' => false,
                ], [
                    'title' => 'New Field',
                    'widthFactor' => 2,
                    'labelPosition' => LabelPosition::LABEL_POSITION_TOP,
                    'newLine' => false,
                ],
                [
                    'title' => 'Title',
                    'widthFactor' => 4,
                    'labelPosition' => LabelPosition::LABEL_POSITION_RIGHT,
                    'newLine' => false,
                ], [
                    'title' => 'Title',
                    'widthFactor' => 4,
                    'labelPosition' => LabelPosition::LABEL_POSITION_RIGHT,
                    'newLine' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerConstruct
     *
     * @param array $inputParams
     * @param array $outputParams
     */
    public function test__construct(array $inputParams, array $outputParams)
    {
        $settings = new SettingsFieldForm($inputParams);

        $this->assertEquals($settings->title, $outputParams['title']);
        $this->assertEquals($settings->getWidthFactor(), $outputParams['widthFactor']);
        $this->assertEquals($settings->getLabelPosition(), $outputParams['labelPosition']);
        $this->assertEquals($settings->newLine, $outputParams['newLine']);
    }

    /**
     * Провайдер для проверка выводов ошибок для сущности.
     *
     * @return array
     */
    public function providerSetSlug()
    {
        return [
            [HiddenField::$nameHideField, \Yii::t('forms', 'field_identifier_forbidden_use')],
            [BlockJs::$nameField, \Yii::t('forms', 'field_identifier_forbidden_use')],
            ['test', ''],
        ];
    }

    /**
     * @dataProvider providerSetSlug
     *
     * @param string $slug
     * @param string $messageExcept
     */
    public function testSetSlug(string $slug, string $messageExcept)
    {
        if ($messageExcept) {
            $this->expectException(UserException::class);
            $this->expectExceptionMessage($messageExcept);
        }

        new SettingsFieldForm(['slug' => $slug]);
    }
}
