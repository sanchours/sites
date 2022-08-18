<?php

namespace skewer\build\Page\Auth;

use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUser;
use skewer\components\auth\CurrentUserPrototype;
use skewer\components\auth\models\Users;
use skewer\components\design\Design;
use skewer\components\forms\FormBuilder;
use skewer\components\forms\service\FormService;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;
use skewer\libs\ulogin\ULogin;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Модуль регистрации и авторизации
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    public $mini_auth = 0;

    public $head = false;

    /** @var string Шаблон для детальной лк */
    public $sTemplate = 'detail.twig';

    private $authSection = 0;

    public $miniAuthHeadTpl = 'AuthFormMiniHead.twig';

    /** @var int Выпадающая форма авторизации? */
    public $dropDown = 0;

    /** @var FormService $_formService */
    private $_formService;

    /**
     * Отдает флаг использования правил разбора url.
     *
     * @return bool
     */
    public function useRouting()
    {
        return !$this->mini_auth;
    }

    /**
     * Прототип - выполняется до вызова метода Execute.
     */
    public function init()
    {
        parent::init();

        $this->authSection = \Yii::$app->sections->getValue('auth');
        $this->_formService = new FormService();
    }

    public function execute()
    {
        $this->setData('page', \Yii::$app->sections->auth());

        $this->setData('profile_url', Api::getProfilePath());
        $this->setData('auth_url', Api::getAuthPath());

        if (Design::modeIsActive()) {
            $this->setData('designMode', Design::getDirList());
        }

        if ($this->head && $this->mini_auth) {
            $this->setTemplate($this->miniAuthHeadTpl);
        } else {
            if (isset($_SERVER['HTTP_REFERER']) && !count($_POST)) {
                Url::remember(
                    $this->getPreviousUrl(),
                    'returnUrlFromAuth'
                );
            }

            $this->setTemplate($this->sTemplate);
        }

        if ($this->mini_auth) {
            $this->actionIndex();

            return psComplete;
        }

        return parent::execute();
    }

    /**
     * Выводит форму авторизации.
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function actionShowAuthForm()
    {
        $authEntity = new AuthEntity(
            $this->authSection,
            $this->getPost()
        );

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $authEntity,
            $this->sectionId(),
            $label
        );

        if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
            $this->setData('current_user', CurrentUser::getUserData());
            Auth::reloadPolicy('public');
            $previous = Url::previous('returnUrlFromAuth');
            \Yii::$app->getResponse()
                ->redirect(($previous) ?: Api::getProfilePath())
                ->send();
            exit;
        }
        if ($this->mini_auth) {
            $formBuilder->setFormTemplate('AuthFormMini.twig');
            $formBuilder->pathDirByTmp = __DIR__ . '/templates';
            $form = $formBuilder->getFormTemplate();

            if (($this->head) && (!$this->dropDown)) {
                $sTpl = 'AuthFormMiniHead.twig';
                $aParams = [
                        'page' => $this->authSection,
                        'url' => \Yii::$app->router->rewriteURL("[{$this->authSection}]"),
                    ];
                $form = site_module\Parser::parseTwig(
                    $sTpl,
                    $aParams,
                    __DIR__ . '/templates'
                    );
            }
        } else {
            $form = $formBuilder->getFormTemplate();
        }

        $this->setData('form', $form);

        return psComplete;
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    protected function actionIndex()
    {
        if (CurrentUser::isLoggedIn()) {
            $this->setData('current_user', CurrentUser::getUserData());
        } else {
            $this->actionShowAuthForm();
        }

        return psComplete;
    }

    /**
     * Регистрация нового пользователя.
     *
     * @throws \Exception
     */
    protected function actionRegister()
    {
        if (CurrentUser::isLoggedIn()) {
            $this->setData('current_user', CurrentUser::getUserData());
        } else {
            $regEntity = new RegUserEntity(
                $this->authSection,
                $this->getPost()
            );

            $label = $this->get('label') ?: $this->oContext->getLabel();
            $formBuilder = new FormBuilder(
                $regEntity,
                $this->sectionId(),
                $label
            );

            if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
                $activateStatus = SysVar::get('auth.activate_status');
                if ($activateStatus == Api::ACTIVATE_AUTO) {
                    $this->setData('msg', \Yii::t('auth', 'msg_instruct_auth'));
                    $this->actionShowAuthForm();
                } else {
                    $title = \Yii::t('auth', 'head_register');
                    site\Page::setTitle($title);
                    site\Page::setAddPathItem(
                        $title,
                        Api::getUrlRegisterPage()
                    );

                    if ($activateStatus == Api::ACTIVATE_EMAIL) {
                        $this->setData(
                            'msg',
                            \Yii::t('auth', 'msg_instruct_reg')
                        );
                    } elseif ($activateStatus == Api::ACTIVATE_ADMIN) {
                        $this->setData(
                            'msg',
                            \Yii::t('auth', 'msg_instruct_admin')
                        );
                    }

                    return;
                }
            } else {
                $this->setData('form', $formBuilder->getFormTemplate());
            }
        }
    }

    /**
     * Форма ввода нового пароля.
     *
     * @throws \Exception
     */
    protected function actionNewPassForm()
    {
        $token = $this->getStr('token');
        $ticket = AuthTicket::get($token);

        $title = \Yii::t('auth', 'head_restore');
        site\Page::setTitle($title);
        site\Page::setAddPathItem($title, Api::getAuthPath());

        if (
            $ticket
            && $ticket->moduleNameIs('auth')
            && $ticket->actionNameIs('recover_pass')
            && $ticket->getObjectId()
        ) {
            $idUser = $ticket->getObjectId();

            /** @var Users $user */
            $user = Users::findOne(['id' => $idUser, 'active' => 1]);

            if ($user) {
                $newPassEntity = new NewPassEntity(
                    $user,
                    $this->authSection,
                    $this->getPost()
                );

                $newPassEntity->login = $user->login;
                $newPassEntity->token = $token;

                $label = $this->get('label') ?: $this->oContext->getLabel();

                $formBuilder = new FormBuilder(
                    $newPassEntity,
                    $this->sectionId(),
                    $label
                );

                if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
                    $ticket->delete($token);
                    $this->setData('msg', \Yii::t('auth', 'msg_new_pass'));

                    \Yii::$app->getResponse()
                        ->redirect(Api::getAuthPath())
                        ->send();
                    exit;
                }
                $this->setData(
                    'form',
                    $formBuilder->getFormTemplate()
                    );
            } else {
                $this->setData(
                    'msg',
                    \Yii::t('auth', 'msg_active_user_not_found')
                );
            }
        } else {
            $this->setData('msg', \Yii::t('auth', 'msg_error_token'));
        }
    }

    /**
     * Восстановление пароля.
     *
     * @throws \Exception
     */
    protected function actionRecover()
    {
        if (CurrentUser::isLoggedIn()) {
            $this->setData('current_user', CurrentUser::getUserData());
        } else {
            $recoveryEntity = new RecoverEntity(
                $this->authSection,
                $this->getPost()
            );

            $sTitle = \Yii::t('auth', 'restore');
            site\Page::setTitle($sTitle);
            site\Page::setAddPathItem($sTitle, Api::getUrlRecoverPage());

            $label = $this->get('label') ?: $this->oContext->getLabel();
            $formBuilder = new FormBuilder(
                $recoveryEntity,
                $this->sectionId(),
                $label
            );

            if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
                $this->setData(
                    'msg',
                    \Yii::t('auth', 'msg_recover_instruct')
                );
            } else {
                $this->setData('form', $formBuilder->getFormTemplate());
            }
        }
    }

    /**
     * выход из системы.
     */
    protected function actionLogout()
    {
        CurrentUser::logout();

        /*
         * @fixme перезагрузка политик не отрабатывает как нужно. С политиками надо разбираться. Закрыл пока переадресацией
         */

        if (isset($_SERVER['HTTP_REFERER'])) {
            \Yii::$app->getResponse()->redirect($this->getPreviousUrl())->send();
        } else {
            \Yii::$app->getResponse()->redirect(Tree::getSectionAliasPath($this->getEnvParam('sectionId')))->send();
        }
    }

    /**
     * Активация аккаунта.
     */
    protected function actionAccountActivation()
    {
        $sToken = $this->getStr('token', '');

        if (!$sToken) {
            $this->setData('msg', \Yii::t('auth', 'msg_error_token'));

            return false;
        }

        $oTicket = AuthTicket::get($sToken);

        if (!$oTicket) {
            $this->setData('msg', \Yii::t('auth', 'msg_error_token'));

            return false;
        }

        if ($oTicket && $oTicket->moduleNameIs('auth') && $oTicket->actionNameIs('activate') && $oTicket->getObjectId()) {
            $iUserId = $oTicket->getObjectId();

            /**
             * @var PageUsers
             */
            $oUser = PageUsers::findOne(['id' => $iUserId, 'active' => '0']);

            if (!$oUser) {
                $this->setData('msg', \Yii::t('auth', 'msg_error_token'));

                return false;
            }

            if (Api::accountActivate($oUser)) {
                $oTicket->delete($sToken);
                $this->setData('msg', \Yii::t('auth', 'msg_verifed'));
                $this->actionShowAuthForm();
            } else {
                $this->setData('msg', \Yii::t('auth', 'msg_error_token'));

                return false;
            }
        } else {
            $this->setData('msg', \Yii::t('auth', 'msg_error_token'));

            return false;
        }
    }

    public function actionUlogin()
    {
        $attr = ULogin::getUserAttributes($this->get('token'));
        if (isset($attr['email'], $attr['network'], $attr['uid'])) {
            $user = Users::findOne(['email' => $attr['email']]);

            if (empty($user)) {
                $user = $this->saveUserFromSocialNetwork($attr);
            } elseif ($user->network && $user->network != $attr['network']) {
                return $this->setErrorInFlashMessage(
                    \Yii::t('socialNetwork', 'err_other_network', [
                        'network' => $user->network,
                    ])
                );
            } elseif (empty($user->network)) {
                return $this->setErrorInFlashMessage(
                    \Yii::t('socialNetwork', 'err_entrance_simple_auth', [
                        'network' => $user->network,
                    ])
                );
            }

            if (CurrentUserPrototype::loginNetwork(
                $user->email,
                $attr['uid'],
                $user->network
            )) {
                \Yii::$app->response->redirect('/auth')->send();
                \Yii::$app->end();
            }
        }

        return $this->setErrorInFlashMessage(
            \Yii::t('socialNetwork', 'err_not_allowed_entrance')
        );
    }

    private function saveUserFromSocialNetwork($attrULogin)
    {
        $user = new Users();
        $user->network = $attrULogin['network'];
        $user->email = $attrULogin['email'];
        $user->pass = Auth::buildPassword($user->network, $attrULogin['uid']);
        $user->login = $user->email;
        $user->active = 1;
        $user->phone = ArrayHelper::getValue($attrULogin, 'phone');
        $user->name = ArrayHelper::getValue($attrULogin, 'last_name')
            . ' '
            . ArrayHelper::getValue($attrULogin, 'first_name');
        $user->save();

        $sBody = ModulesParams::getByName('auth', 'mail_admin_activate');
        $aParams['link'] = site\Site::admUrl('Auth');
        Mailer::sendMailAdmin(
            ModulesParams::getByName('auth', 'mail_title_admin_newuser'),
            $sBody,
            $aParams
        );

        return $user;
    }

    private function setErrorInFlashMessage($message)
    {
        \Yii::$app->session->setFlash('errSocialNetwork', $message);

        return $this->actionShowAuthForm();
    }

    /**
     * Возвращает относительную ссылку страницы
     * с которой пришел пользователь
     * @return string
     */
    private function getPreviousUrl()
    {
        if (!isset($_SERVER['HTTP_REFERER']))
            return '';
        $sUrlPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        $sQuery = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);

        //проверяем пришёл ли пользователь с формы восстановления пароля
        //если да, то отправляем на главную
        if (preg_match('/^cmd=(.*)&.*$/', $sQuery, $matches)) {
            if ($matches[1] == NewPassEntity::getCmd()) {
                return '/';
            }
        }

        if ($sQuery) {
            $sUrlPath .= "?$sQuery";
        }
        return $sUrlPath;
    }
}
