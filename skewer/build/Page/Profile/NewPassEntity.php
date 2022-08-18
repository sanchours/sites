<?php

declare(strict_types=1);

namespace skewer\build\Page\Profile;

use skewer\build\Page\Auth\Api;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\models\Users;
use skewer\components\forms\components\fields\Password;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;
use skewer\helpers\Mailer;

/**
 * This is parameters of required fields for this form.
 *
 * @property string $oldpass
 * @property string $pass
 * @property string $wpass
 */
class NewPassEntity extends BuilderEntity
{
    public $cmd = 'settings';
    public $redirectKeyName = 'newPassForm';

    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'oldpass',
                'title' => 'auth.old_pass',
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
                'newLine' => 1,
            ],
            'type' => [
                'name' => Password::class,
                'typeOfValid' => Text::class,
            ],
        ],
    ];

    public static function tableName(): string
    {
        return 'prof_new_pass_form';
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('auth', 'change_form');
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
     * @param string $formHash
     *
     * @return bool
     */
    public function validate(string $formHash): bool
    {
        if (parent::validate($formHash)) {
            if ($this->oldpass && $this->checkOldPass()) {
                if ($this->pass && $this->wpass) {
                    if (mb_strlen($this->pass) < 6) {
                        $this->getField('pass')
                            ->addError(
                                'err_short_pass',
                                \Yii::t('auth', 'err_short_pass')
                            );
                    }

                    if ($this->pass != $this->wpass) {
                        $this->getField('wpass')
                            ->addError(
                                'err_pass_not_mutch',
                                \Yii::t('auth', 'err_pass_not_mutch')
                            );
                    }

                    return !$this->hasFieldsError();
                }
            } else {
                $this->getField('oldpass')
                    ->addError(
                        'incorrect_old_pass',
                        \Yii::t('auth', 'incorrect_old_pass')
                    );
            }
        }

        return false;
    }

    public function checkOldPass(): bool
    {
        if (!CurrentUser::isLoggedIn()) {
            return false;
        }

        $oUser = Users::findOne(CurrentUser::getId());

        return $oUser->pass == Auth::buildPassword(
            $oUser->login,
            $this->oldpass
            );
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        $sPass = $this->getField('pass')->value;
        $oItem = Users::findOne(CurrentUser::getId());
        $oItem->pass = Auth::buildPassword($oItem->login, $sPass);
        if ($oItem->save()) {
            Mailer::sendMail(
                $oItem->email,
                \Yii::t('data/auth', 'mail_title_new_pass'),
                \Yii::t('data/auth', 'mail_new_pass')
            );

            return parent::save();
        }

        return false;
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = Api::getProfilePath();
    }
}
