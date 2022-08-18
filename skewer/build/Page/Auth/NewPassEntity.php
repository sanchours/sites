<?php

declare(strict_types=1);

namespace skewer\build\Page\Auth;

use skewer\components\auth\models\Users;
use skewer\components\forms\components\fields\Password;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;

/**
 * This is parameters of required fields for this form.
 *
 * @property string $pass
 * @property string $wpass
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class NewPassEntity extends BuilderEntity
{
    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'pass',
                'title' => 'auth.new_pass',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Password::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'wpass',
                'title' => 'auth.wpassword',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 0,
            ],
            'type' => [
                'name' => Password::class,
                'typeOfValid' => Text::class,
            ],
        ],
    ];

    public $login = '';
    public $token = '';

    public $cmd = 'newPassForm';
    public $redirectKeyName = 'newPassForm';

    private $_idAuthSection;
    private $_users;

    public function __construct(
        Users $users,
        int $idAuthSection = null,
        array $innerData = [],
        array $config = []
    ) {
        $this->_idAuthSection = $idAuthSection;
        $this->_users = $users;

        parent::__construct($innerData, $config);
    }

    public static function tableName(): string
    {
        return 'new_pass_form';
    }

    public static function getCmd()
    {
        return 'newPassForm';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('auth', 'newpass_form');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'auth.send';

        $formAggregate->settings->showHeader = 0;
        $formAggregate->protection->captcha = true;
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
    public function validate(string $formHash): bool
    {
        if (parent::validate($formHash)) {
            if ($this->pass && $this->wpass) {
                if (mb_strlen($this->pass) < 6) {
                    $this
                        ->getField('pass')
                        ->addError(
                            'err_short_pass',
                            \Yii::t('auth', 'err_short_pass')
                        );
                }

                if ($this->pass != $this->wpass) {
                    $this
                        ->getField('wpass')
                        ->addError(
                            \Yii::t(
                                'err_pass_not_mutch',
                                'auth',
                                'err_pass_not_mutch'
                            )
                        );
                }

                return !$this->hasFieldsError();
            }
        }

        return false;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        return Api::saveNewPass($this->_users, $this->pass) && parent::save();
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = \Yii::$app->router->rewriteURL(
            "[{$this->_idAuthSection}]"
        );

        $templateForm->paramsForInputTemplate = [
            'login' => $this->login,
            'token' => $this->token,
        ];
    }
}
