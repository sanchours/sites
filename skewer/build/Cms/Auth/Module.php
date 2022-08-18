<?php

namespace skewer\build\Cms\Auth;

use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Cms;
use skewer\build\Page\Auth\Api;
use skewer\build\Page\Auth\AuthTicket;
use skewer\build\Tool\LeftList\Group;
use skewer\build\Tool\UnderConstruction\Api as ApiUnderConst;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\models\LogErrAuth;
use skewer\components\auth\models\Users;
use skewer\components\auth\Policy;
use skewer\components\forms\components\protection\Captcha;
use skewer\helpers\Mailer;
use skewer\libs\Compress\ChangeAssets;
use yii\validators\EmailValidator;
use yii\web\ForbiddenHttpException;

/**
 * Класс для авторизации в CMS
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    protected $viewMode = 'default';

    private $dictKeys = [
        'error'
    ];

    protected function preExecute()
    {
        $this->addInitParam('dict', $this->parseLangVars($this->dictKeys, 'adm'));

        return psComplete;
    }

    /**
     * Разрешает выполнение модуля без авторизации.
     *
     * @return bool
     */
    public function allowExecute()
    {
        return true;
    }

    /**
     * Выбор интерфейса для отображения в текущей ситуации.
     */
    protected function actionInit()
    {
        if ($this->viewMode === 'form') {
            $this->showAuthForm();
        } else {
            $this->showMainPanel();
        }
    }

    /**
     * Отдает на выход форму авторизации в собственном слое.
     */
    protected function showAuthForm()
    {
        $this->setModuleLangValues(
            [
                'authPanelTitle',
                'authLoginTitle',
                'authPassTitle',
                'authLoginButton',
                'authForgotPass',
                'authCanapeId',
                'authLoginIncorrect',
            ]
        );
        $this->addInitParam('sNameForm', 'AuthForm');

        // основная библиотека вывода
        $this->setJSONHeader('externalLib', 'AuthLayout');

        if (ApiUnderConst::isShowBlock()) {
            $this->setData('reload', true);
        }
        // добавить библиотеку
        $this->addLibClass('AuthForm');

        // задать состояние
        $this->setCmd('init');

        // сообщение
        $aMes = \Yii::$app->getParam('systemMessage.cms.message');
        if ($aMes) {
            $this->addMessage(
                $aMes[0],
                $aMes[1],
                -1
            );
        }
    }

    /**
     * Отдает панель для админского интерфейса с данными авторизации.
     */
    protected function showMainPanel()
    {
        $this->setModuleLangValues(
            [
                'authLogoutButton',
                'authLastVisit',
                'cache_flag_on',
                'cache_flag_off',
                'debug_flag_on',
                'debug_flag_off',
                'compression_flag_off',
                'compression_flag_on',
            ]
        );

        $aUserData = $this->getUserData();

        $this->addInitParam('renderData', [
            'username' => $aUserData['username'],
            'lastlogin' => $aUserData['lastlogin'],
            'showAdmSwitcher' => CurrentAdmin::isSystemModeByUserData(),
            'admSwitcherVal' => CurrentAdmin::isTempAdminMode(),
            'changeCacheMode' => CurrentAdmin::getCacheMode(),
            'changeDebugMode' => CurrentAdmin::getDebugMode(),
            ChangeAssets::NAMEPARAM => (bool)SysVar::get(ChangeAssets::NAMEPARAM, 1),
        ]);
    }

    /**
     * Взводит флаг "обычный администратор"
     * @throws ForbiddenHttpException
     */
    protected function actionSetAdminMode() {
        $bMode = $this->getStr('mode');

        if (!CurrentAdmin::isSystemModeByUserData())
            throw new ForbiddenHttpException('access denied');

        CurrentAdmin::setTempAdminMode($bMode);
    }

    /**
     * Взводит флаг для сброса кэша
     * @throws ForbiddenHttpException
     */
    protected function actionSetCacheMode()
    {
        if (!CurrentAdmin::isSystemModeByUserData()) {
            throw new ForbiddenHttpException('access denied');
        }
        CurrentAdmin::changeCacheMode();
        $aUserData = $this->getUserData();
        $this->addInitParam('renderData', [
            'username' => $aUserData['username'],
            'lastlogin' => $aUserData['lastlogin'],
            'showAdmSwitcher' => CurrentAdmin::isSystemModeByUserData(),
            'admSwitcherVal' => CurrentAdmin::isTempAdminMode(),
            'changeCacheMode' => CurrentAdmin::getCacheMode(),
            'changeDebugMode' => CurrentAdmin::getDebugMode(),
        ]);
    }

    /**
     * Взводит флаг debug режима для текщего пользователя
     * @throws ForbiddenHttpException
     */
    protected function actionSetDebugMode()
    {
        if (!CurrentAdmin::isSystemModeByUserData()) {
            throw new ForbiddenHttpException('access denied');
        }
        CurrentAdmin::changeDebugMode();
        $aUserData = $this->getUserData();
        $this->addInitParam('renderData', [
            'username' => $aUserData['username'],
            'lastlogin' => $aUserData['lastlogin'],
            'showAdmSwitcher' => CurrentAdmin::isSystemModeByUserData(),
            'admSwitcherVal' => CurrentAdmin::isTempAdminMode(),
            'changeCacheMode' => CurrentAdmin::getCacheMode(),
            'changeDebugMode' => CurrentAdmin::getDebugMode(),
        ]);
    }

    protected function actionSetCompressionMode()
    {
        if (!CurrentAdmin::isSystemModeByUserData()) {
            throw new ForbiddenHttpException('access denied');
        }

        $compression = SysVar::get(ChangeAssets::NAMEPARAM, 1);
        SysVar::set(ChangeAssets::NAMEPARAM, $compression ? 0 : 1);

        $aUserData = $this->getUserData();
        $this->addInitParam('renderData', [
            'username' => $aUserData['username'],
            'lastlogin' => $aUserData['lastlogin'],
            'showAdmSwitcher' => CurrentAdmin::isSystemModeByUserData(),
            'admSwitcherVal' => CurrentAdmin::isTempAdminMode(),
            'changeCacheMode' => CurrentAdmin::getCacheMode(),
            'changeDebugMode' => CurrentAdmin::getDebugMode(),
            ChangeAssets::NAMEPARAM => (bool)SysVar::get(ChangeAssets::NAMEPARAM, 1),
        ]);
    }

    /**
     * Авторизация пользователя.
     *
     * @return int
     */
    protected function actionLogin()
    {
        // получение данных
        $sLogin = mb_strtolower($this->getStr('login'));
        $sPass = $this->getStr('pass');

        //Проверка на ошибки
        $errLog = LogErrAuth::getEntry($sLogin);
        $this->setCmd('login');
        $bLogIn = ($errLog) ? false : true;
        if ($bLogIn) {
            // попытка авторизации
            $bLogIn = CurrentAdmin::login($sLogin, $sPass);
            $notice = ($bLogIn) ? \Yii::t('auth', 'user_login') : \Yii::t('auth', 'user_invalid_login');
            $this->addModuleNoticeReport($notice, ['User ID' => CurrentAdmin::getId(), 'Login' => $sLogin]);
            if (!$bLogIn) {
                LogErrAuth::addToLogErr($sLogin);
            }
        } else {
            $sTimeReset = (timeExcess) ?: 5;
            $notice = str_replace('{time}', $sTimeReset, $errLog);
            $this->addModuleNoticeReport($errLog, ['User ID' => CurrentAdmin::getId(), 'Login' => $sLogin]);
        }

        // результат авторизации
        $this->setData('notice', $notice);
        $this->setData('success', $bLogIn);

        // отдать результат работы метода
        return psComplete;
    }

    /**
     * Выход из системы.
     *
     * @return int
     */
    protected function actionLogout()
    {
        // задать состояние
        $this->setCmd('login');

        $this->addModuleNoticeReport(
            \Yii::t('auth', 'user_logout'),
            [
                'User ID' => CurrentAdmin::getId(),
                'Login' => CurrentAdmin::getLogin(),
            ]
        );

        // попытка авторизации
        $bLogOut = CurrentAdmin::logout();

        // результат авторизации
        $this->setData('success', $bLogOut);

        // отдать результат работы метода
        return $bLogOut ? psReset : psComplete;
    }

    /**
     * Забыли пароль?
     */
    protected function actionForgotPass()
    {
        $this->setData(
            'lang',
            $this->setModuleLangValues([
                'email_forgot',
                'forgotIncorrectLogin',
                'forgotLoginPass',
                'forgotPass',
                'forgotSend',
                'passwords_recovery',
                'back_check',
            ])
        );

        $this->addInitParam('sNameForm', 'ForgotPass');
        // добавить библиотеку
        $this->addLibClass('ForgotPass');

        // задать состояние
        $this->setCmd('ForgotPass');
    }

    /**
     * Проверка формы "Забыли пароль".
     *
     * @return int
     */
    protected function actionCheckForgot()
    {
        // получение данных
        $sLogin = mb_strtolower($this->getStr('login'));
        $sCaptcha = $this->getStr('captcha');

        $bLogIn = $this->findErrorForgotPass($sLogin);
        //проверка капчи
        $bCaptcha = Captcha::check($sCaptcha);
        if (!$bCaptcha) {
            $notice = \Yii::t('forms', 'wrong_captcha');
            $this->setData('captcha', $notice);
            $bLogIn = false;
        }
        $this->setData('success', $bLogIn);
        if ($bLogIn) {
            $this->setData(
                'lang',
                $this->setModuleLangValues([
                    'msg_recover_instruct',
                    'back_check',
                    'passwords_recovery',
                ])
            );

            $this->addInitParam('sNameForm', 'CheckForgot');
            // добавить библиотеку
            $this->addLibClass('Success');

            // задать состояние
            $this->setCmd('Success');
            $sBody = \Yii::t('data/auth', 'mail_reset_password');

            $sLogInAdmin = Site::getAdminEmail();
            $oUser = ($sLogInAdmin == $sLogin) ? Api::getUserByLogin(Group::ADMIN) : Api::getUserByEmail($sLogin);
            if ($oUser) {
                $oTicket = new AuthTicket();
                $oTicket->setModuleName('auth');
                $oTicket->setActionName('recover_admin_pass');

                $oTicket->setObjectId($oUser->id);
                $sToken = $oTicket->insert();
                $aParams['link'] = Site::httpDomain() . '/admin?cmd=newPassForm&token=' . $sToken;
                Mailer::sendMail($sLogin, \Yii::t('data/auth', 'mail_title_reset_password'), $sBody, $aParams);
            }
        } else {
            $this->setCmd('checkForgot');
        }

        return psComplete;
    }

    /**
     * Проверка на наличие ошибок в форме востановления пароля.
     *
     * @param string $sLogin
     *
     * @return bool
     */
    private function findErrorForgotPass($sLogin)
    {
        $oValidator = new EmailValidator();
        if ($oValidator->validate($sLogin)) {
            // проверка логина
            $sLogInAdmin = Site::getAdminEmail();
            $aDataUser = Users::find()->where(['email' => $sLogin, 'active' => 1])->one();
            if ($aDataUser) {
                $aPolicy = Policy::getPolicyDetail($aDataUser['group_policy_id']);
                if ($aPolicy['access_level'] == Policy::POLICY_ADMIN_USERS) {
                    return true;
                }
            } elseif ($sLogin == $sLogInAdmin) {
                return true;
            }
        }
        $notice = \Yii::t('auth', 'forgotIncorrectLogin');
        $this->setData('login', $notice);
        $this->addModuleNoticeReport($notice, ['Login' => $sLogin]);

        return false;
    }

    /*
     * Проверка токена для отображение формы ввода нового пароля
     */
    protected function actionNewPassForm()
    {
        $sToken = $this->get('token');
        $oTicket = AuthTicket::get($sToken);
        if ($oTicket) {
            $iUserId = $oTicket->getObjectId();
            /** @var Users $oItem */
            $oItem = Users::findOne($iUserId);
            if ($oItem) {
                $this->setDataForRecoveryForm();
            }
        } else {
            $aLang = $this->setModuleLangValues([
                'back_check',
                'passwords_recovery',
            ]);
            $aLang['msg_recover_instruct'] = \Yii::t('auth', 'msg_error_token');
            $this->setData('lang', $aLang);
            $this->addLibClass('Success');
            $this->setCmd('Success');
        }

        return false;
    }

    /**
     * Проверка новых паролей и токена.
     *
     * @return bool
     */
    protected function actionRecoveryPass()
    {
        $sPass = $this->getStr('password');
        $sWPass = $this->getStr('wpassword');
        $sToken = $this->getStr('token');

        if ($sPass && $sWPass) {
            if (mb_strlen($sPass) < 6) {
                $this->setData('notice', \Yii::t('auth', 'err_short_pass'));
            } elseif ($sPass == $sWPass) {
                $oTicket = AuthTicket::get($sToken);
                $iUserId = $oTicket->getObjectId();

                /** @var Users $oItem */
                $oItem = Users::findOne($iUserId);
                if ($oItem) {
                    $oItem->pass = Auth::buildPassword($oItem->login, $sPass);
                    $oItem->save();
                    //Отправка уведомления на почту
                    $sEmail = ($oItem->login == Group::ADMIN) ? Site::getAdminEmail() : $oItem->email;
                    Mailer::sendMail(
                        $sEmail,
                        \Yii::t('data/auth', 'mail_title_new_pass'),
                        \Yii::t('data/auth', 'mail_new_pass')
                    );
                    //очистка тикета
                    $oTicket->delete($sToken);

                    $aLang = $this->setModuleLangValues([
                        'back_check',
                        'passwords_recovery',
                    ]);
                    $aLang['msg_recover_instruct'] = \Yii::t('auth', 'msg_new_pass');
                    $this->setData('lang', $aLang);
                    $this->addLibClass('Success');
                    $this->setData('success', true);
                    $this->setCmd('Success');

                    return true;
                }
                $this->setData('notice', \Yii::t('auth', 'msg_not_found_user'));
            } else {
                $this->setData('notice', \Yii::t('auth', 'error_pass_fields'));
            }
        } else {
            $this->setData('notice', \Yii::t('auth', 'error_pass_fields'));
        }

        $this->setDataForRecoveryForm();
        $this->setData('success', false);

        return false;
    }

    private function setDataForRecoveryForm()
    {
        $this->setData(
            'lang',
            $this->setModuleLangValues([
                'new_pass',
                'wpassword',
                'send',
                'back_check',
                'passwords_recovery',
            ])
        );

        $this->addInitParam('sNameForm', 'RecoveryForm');
        $this->addLibClass('RecoveryForm');
        $this->setCmd('RecoveryForm');
    }

    /**
     * Получает имя и дату последнего посещения об авторизованном пользователе
     * @return array
     */
    private function getUserData()
    {
        $aUserData = CurrentAdmin::getUserData();

        /*Забираем из сессии данные о предыдущем входе*/
        if (\Yii::$app->session->get('lastlogin') !== null) {
            $aUserData['lastlogin'] = \Yii::$app->session->get('lastlogin');
        }

        /*Если есть данные о пользователе CanapeId*/
        if (\Yii::$app->session->get('current_canape_id_login') !== null) {
            $aCanapeIdData = \Yii::$app->session->get('current_canape_id_login');
            $sUsername = ($aCanapeIdData['email'] ?? 'sys') . ' [' . $aCanapeIdData['auth_mode'] . '/canape-id]';
        } else {
            $sUsername = (isset($aUserData['name'])) ? $aUserData['name'] : '';
        }

        $sDate = $aUserData['lastlogin'] ?? '';
        if ($sDate and $sDate > 1900) {
            $sDate = date('d.m.Y H:i:s', strtotime($sDate));
        } else {
            $sDate = \Yii::t('auth', 'firstVisit');
        }

        return [
            'username' => $sUsername,
            'lastlogin' => $sDate,
        ];
    }
}
