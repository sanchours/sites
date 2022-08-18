<?php

declare(strict_types=1);

namespace skewer\build\Page\Profile;

use skewer\components\auth\CurrentUser;
use skewer\components\auth\models\Users;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;

class EditProfileEntity extends BuilderEntity
{
    public $cmd = 'info';
    public $redirectKeyName = 'editProfile';

    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'name',
                'title' => 'auth.fio',
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
                'slug' => 'postcode',
                'title' => 'auth.postcode',
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
                'slug' => 'address',
                'title' => 'auth.address',
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
                'title' => 'auth.contact_phone',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
                'specStyle' => 'data-mask="phone"',
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
            ],
        ],
    ];

    private $_emptyInnerData = true;

    public function __construct(array $innerData = [], array $config = [])
    {
        $this->_emptyInnerData = empty($innerData);

        $user = Users::findOne(CurrentUser::getId());

        $editData = [
            'name' => $innerData['name'] ?? $user->name,
            'postcode' => $innerData['postcode'] ?? $user->postcode,
            'address' => $innerData['address'] ?? $user->address,
            'phone' => $innerData['phone'] ?? $user->phone,
        ];

        foreach ($innerData as $key => $value) {
            if (!isset($editData[$key])) {
                $editData[$key] = $value;
            }
        }

        parent::__construct($editData, $config);
        //необходимо установить переденные параметры для отображения
        $this->setValues();
    }

    public static function tableName(): string
    {
        return 'edit_profile_form';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('auth', 'contact_form');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'auth.save';

        $formAggregate->settings->showHeader = 0;
        $formAggregate->protection->captcha = false;
        $formAggregate->license->agree = 0;

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_METHOD;

        $formAggregate->save();
        $formAggregate->saveExtraData();

        self::createFields($formAggregate->getIdForm());
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function save(): bool
    {
        $oUser = Users::findOne(CurrentUser::getId());
        $oUser->name = $this->getField('name')->value;
        $oUser->postcode = $this->getField('postcode')->value;
        $oUser->address = $this->getField('address')->value;
        $oUser->phone = $this->getField('phone')->value;

        if ($oUser->save() && parent::save()) {
            return true;
        }

        $errors = $oUser->getErrors();
        if ($errors) {
            foreach ($errors as $name => $error) {
                $error = is_array($error) ? current($error) : $error;

                $field = $this->getField($name);
                if ($field instanceof FieldAggregate) {
                    $field->addError($name, $error);
                } else {
                    $this->setError($error);
                }
            }
        }

        return false;
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = '';
    }

    public function hasSendData(): bool
    {
        return !$this->_emptyInnerData;
    }
}
