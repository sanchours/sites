<?php

declare(strict_types=1);

namespace skewer\build\Page\Auth;

use skewer\base\site\Page;
use skewer\components\auth\models\Users;
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
 * This is parameters of required fields for this form.
 *
 * @property string $login
 * @property string $password
 * @property string $wpassword
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class RegUserEntity extends BuilderEntity
{
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
        [
            'settings' => [
                'slug' => 'wpassword',
                'title' => 'auth.wpassword',
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

    public static $nameForm = 'reg_user_form';
    public $cmd = 'register';
    public $redirectKeyName = 'regForm';

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
        return 'reg_user_form';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('auth', 'head_register');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'auth.send';
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
        if (parent::validate($formHash)) {
            if ($this->login && $this->password && $this->wpassword) {
                if (mb_strlen($this->password) < 6) {
                    $this
                        ->getField('password')
                        ->addError(
                            'password',
                            \Yii::t('auth', 'err_short_pass')
                        );
                }

                if ($this->password != $this->wpassword) {
                    $this
                        ->getField('wpassword')
                        ->addError(
                            'wpassword',
                            \Yii::t('auth', 'err_pass_not_mutch')
                        );
                }

                $sLogin = mb_strtolower($this->login);
                $oItem = Users::findOne(['login' => $sLogin]);

                if ($oItem) {
                    $this
                        ->getField('login')
                        ->addError(
                            'login',
                            \Yii::t('auth', 'alredy_taken')
                        );
                }

                return !$this->hasFieldsError();
            }
        }

        return false;
    }

    /**
     * Сохранение нового пользователя.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function save(): bool
    {
        return Api::registerUser([
                'login' => $this->login,
                'pass' => $this->password,
            ]) && parent::save();
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $sTitle = \Yii::t('auth', 'head_register');
        Page::setTitle($sTitle);
        Page::setAddPathItem($sTitle, Api::getUrlRegisterPage());

        $templateForm->tagAction = \Yii::$app->router->rewriteURL("[{$this->_idAuthSection}]");
        $templateForm->addParam = ApiULogin::getTemplate4AuthSocialNetwork();
    }
}
