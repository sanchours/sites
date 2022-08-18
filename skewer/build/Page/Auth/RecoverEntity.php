<?php

declare(strict_types=1);

namespace skewer\build\Page\Auth;

use skewer\components\auth\Users;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Email;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;

/**
 * This is parameters of required fields for this form.
 *
 * @property string $login
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class RecoverEntity extends BuilderEntity
{
    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'login',
                'title' => 'auth.your_email',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Email::class,
            ],
        ],
    ];

    public $cmd = 'recover';
    public $redirectKeyName = 'recover';

    /**
     * @var PageUsers
     */
    private $_user;

    private $_idAuthSection;

    public function __construct(
        int $idAuthSection = null,
        array $innerData = [],
        array $config = []
    ) {
        $this->_idAuthSection = $idAuthSection;

        parent::__construct($innerData, $config);
    }

    public static function tableName(): string
    {
        return 'recover_form';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('auth', 'recover_form');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'auth.restore';

        $formAggregate->settings->showHeader = 0;
        $formAggregate->protection->captcha = false;
        $formAggregate->license->agree = 0;

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_METHOD;

        $formAggregate->save();
        $formAggregate->saveExtraData();

        self::createFields($formAggregate->getIdForm());
    }

    /**
     * @param string $formHash
     *
     * @return bool
     */
    public function validate(string $formHash): bool
    {
        $validated = parent::validate($formHash);
        if (!$validated) {
            return false;
        }
        $validated = false;

        if (Users::isUsersFromSocialNetwork($this->login)) {
            $this->getField('login')
                ->addError(
                    'msg_not_accept_recover',
                    \Yii::t('auth', 'social_network_user')
                );
        } elseif ($this->login) {
            $this->_user = Api::getUserByLogin($this->login);
            if ($this->_user) {
                if ($this->_user->active) {
                    $validated = true;
                } else {
                    $this->setError(
                        \Yii::t('auth', 'msg_login_user_not_active')
                    );
                }
            }
        } else {
            $this->getField('login')
                ->addError(
                    'msg_not_found_user',
                    \Yii::t('auth', 'msg_not_found_user')
                );
        }

        return $validated;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        return Api::recoverPass($this->_user) && parent::save();
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = \Yii::$app->router->rewriteURL(
            "[{$this->_idAuthSection}]"
        );
        $templateForm->page = $this->_idAuthSection;
    }
}
