<?php

declare(strict_types=1);

namespace skewer\build\Page\Auth;

use skewer\base\site_module\Parser;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\Users;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\fields\Password;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Email;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;
use skewer\libs\ulogin\Api as ApiULogin;

/**
 * Class AuthEntity.
 *
 * @property string $login
 * @property string $password
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class AuthEntity extends BuilderEntity
{
    public $cmd = 'ShowAuthForm';

    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'login',
                'title' => 'auth.login_mail',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Email::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'password',
                'title' => 'auth.password',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Password::class,
                'typeOfValid' => Text::class,
            ],
        ],
    ];

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
        return 'auth_form';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('auth', 'auth_form');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'auth.authLoginButton';

        $formAggregate->settings->showHeader = 0;
        $formAggregate->protection->captcha = false;
        $formAggregate->license->agree = 0;

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_METHOD;

        $formAggregate->save();
        $formAggregate->saveExtraData();

        self::createFields($formAggregate->getIdForm());
    }

    public function validate(string $formHash): bool
    {
        if (!parent::validate($formHash)) {
            return false;
        }

        $socialNetwork = Users::getSocialNetworkByUser($this->login);
        if ($socialNetwork) {
            $this->setError(\Yii::t(
                'auth',
                'not_auth_social_network_user',
                [
                    'social_network' => $socialNetwork,
                ]
            ));

            return false;
        }

        if (PageUsers::findOne(['login' => $this->login, 'active' => 0])) {
            $this->setError(\Yii::t('auth', 'msg_login_user_not_active'));
        } elseif ($this->login && $this->password && CurrentUser::login($this->login, $this->password)) {
            return true;
        } else {
            $this->setError(\Yii::t('auth', 'incorrect_login_or_pass'));
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
        return parent::save();
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = \Yii::$app->router->rewriteURL(
            "[{$this->_idAuthSection}]"
        );
        $templateForm->paramsForButtonTemplate = [
            'page' => $this->_idAuthSection,
        ];
        $templateForm->addParam = ApiULogin::getTemplate4AuthSocialNetwork();
    }

    public function getTemplateWithoutForm($sTpl, $authSection, $sPath)
    {
        $aParams = [
            'page' => $authSection,
            'url' => \Yii::$app->router->rewriteURL("[{$authSection}]"),
        ];

        return Parser::parseTwig($sTpl, $aParams, $sPath);
    }
}
