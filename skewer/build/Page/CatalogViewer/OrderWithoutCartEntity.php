<?php

declare(strict_types=1);

namespace skewer\build\Page\CatalogViewer;

use skewer\build\Page\Forms\FormEntity;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\fields\Textarea;
use skewer\components\forms\components\typesOfValid\Email;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;
use yii\base\ErrorException;

/**
 * Форма заказа товара без корзины.
 *
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class OrderWithoutCartEntity extends FormEntity
{
    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'naimenovanie-tovara',
                'title' => 'order_form_field_goods_name',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
                'maxLength' => 500,
            ],
        ],
        [
            'settings' => [
                'slug' => 'person',
                'title' => 'order_form_field_person',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'phone',
                'title' => 'order_form_field_phone',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 0,
                'specStyle' => 'data-mask="phone"',
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'email',
                'title' => 'order_form_field_email',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 0,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Email::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'text',
                'title' => 'order_form_field_text',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Textarea::class,
                'typeOfValid' => Text::class,
                'maxLength' => 500,
            ],
        ],
    ];

    public static function tableName(): string
    {
        return 'forma-zakaza-tovara';
    }

    /**
     * @param string $lang
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return int
     */
    public static function createTable(string $lang = 'ru'): int
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t(
            'data/order',
            'order_form_title',
            [],
            $lang
        );
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->emailInReply = true;

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_BASE;

        /* Письмо-автоответ */
        $formAggregate->answer->title = \Yii::t(
            'data/order',
            'order_form_add_answer_title',
            [],
            $lang
        );

        $formAggregate->answer->letter = \Yii::t(
            'data/order',
            'order_form_add_answer_body',
            [],
            $lang
        );

        $formAggregate->license->agree = 1;
        $formAggregate->license->setText(\Yii::t('forms', 'agreement_title'));

        $formAggregate->save();
        $formAggregate->saveExtraData();

        foreach (self::$fieldsForCreatedForm as &$field) {
            if (!isset($field['settings']['title'])) {
                throw new ErrorException('В настройках поля отсутствует заголовок');
            }
            $field['settings']['title'] = \Yii::t('data/order', $field['settings']['title'], [], $lang);
        }

        self::createFields($formAggregate->getIdForm());

        return $formAggregate->getIdForm();
    }
}
