<?php

namespace skewer\build\Tool\Auth;

use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Page\Auth\Api;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentUserPrototype;
use skewer\components\auth\models\Users;
use skewer\components\auth\Policy;
use skewer\components\auth\Users as UsersAuth;
use skewer\components\i18n\Languages;
use skewer\components\i18n\Messages;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль настройки вывода форм регистрации
 * Class Module.
 */
class Module extends ModulePrototype
{
    protected $sLanguageFilter = '';

    /** @var array Поля настроек писем,
     * данные метки хранятся в таблице language_values,
     * предназначены для писем по манипуляциями с паролем
     */
    protected $aSettingsKeysPass =
        [
            'mail_title_reset_password',
            'mail_title_new_pass',
            'mail_new_pass',
            'mail_reset_password',
        ];
    /** @var array Поля настроек писем,
     * данные метки хранятся в таблице module_params
     * предназначены для манипуляциями с языковыми метками для писем
     */
    protected $aSettingsKeys =
        [
            'mail_activate',
            'mail_close_ban',
            'mail_banned',
            'mail_admin_activate',
            'mail_user_activate',
            'mail_title_admin_newuser',
            'mail_title_user_newuser',
            'mail_title_mail_activate',
            'mail_title_mail_close_banned',
            'mail_title_mail_banned',
        ];

    protected $iStatusFilter = 0;

    protected $onPage = 20;
    protected $iPageNum = 0;

    // фильтр по тексту
    protected $sSearchNameFilter = '';
    protected $sSearchEmailFilter = '';
    protected $sSearchPhoneFilter = '';

    /**
     * Метод, выполняемый перед action меодом
     */
    protected function preExecute()
    {
        // id текущего раздела
        $this->iStatusFilter = $this->get('filter_status', false);
        $this->sSearchNameFilter = $this->getStr('search');
        $this->sSearchEmailFilter = $this->getStr('email');
        $this->sSearchPhoneFilter = $this->getStr('phone');

        $sLanguage = \Yii::$app->language;

        $this->sLanguageFilter = $this->get('filter_language', $sLanguage);

        $this->iPageNum = $this->getInt('page');
    }

    /**
     * Первичное состояние.
     */
    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Сохраняем состояние.
     */
    protected function actionSaveStatement()
    {
        $iStatus = $this->getInDataValInt('status', 0);
        SysVar::set('auth.activate_status', $iStatus);

        $this->actionList();
    }

    /**
     * Выбор активации пользователя.
     */
    protected function actionEditActivateStatement()
    {
        $this->render(new view\ActivateStatement([
            'list' => Api::getActivateStatusList(),
            'value' => SysVar::get('auth.activate_status'),
        ]));
    }

    protected function actionChangeStatus()
    {
        $iUserId = $this->getInDataValInt('id');
        $iActiveId = $this->getInDataValInt('active');

        $oUser = Users::findOne($iUserId);

        if ($oUser) {
            $prevStatus = $oUser->active;

            $oUser->active = $iActiveId;

            if (!$oUser->validate() or !$oUser->save()) {
                throw new ui\ARSaveException($oUser);
            }
            $sMsg = \Yii::t('auth', 'change_status');
            $sSubject = \Yii::t('auth', 'change_status');

            if ($prevStatus == Api::STATUS_NO_AUTH && $iActiveId == Api::STATUS_AUTH) {
                $sMsg = Api::getTextMailActivate();
                $sSubject = ModulesParams::getByName('auth', 'mail_title_mail_activate');
            }

            if ($prevStatus == Api::STATUS_BANNED && $iActiveId == Api::STATUS_AUTH) {
                $sMsg = Api::getTextMailCloseBan();
                $sSubject = ModulesParams::getByName('auth', 'mail_title_mail_close_banned');
            }

            if ($iActiveId == Api::STATUS_BANNED) {
                $sMsg = Api::getTextMailBanned();
                $sSubject = ModulesParams::getByName('auth', 'mail_title_mail_banned');
            }

            Policy::incPolicyVersion();

            Mailer::sendMail($oUser->email, $sSubject, $sMsg);
        }
        $this->actionList();
    }

    /**
     * Список пользователей в магазине.
     */
    protected function actionList()
    {
        $aClients = Users::find()->where(['group_policy_id' => 3]);

        if ($this->iStatusFilter !== false) {
            $aClients->andWhere(['active' => $this->iStatusFilter]);
        }

        if ($this->sSearchNameFilter) {
            $aClients->andWhere(['like', 'name', $this->sSearchNameFilter]);
        }

        if ($this->sSearchEmailFilter) {
            $aClients->andWhere(['like', 'email', $this->sSearchEmailFilter]);
        }

        if ($this->sSearchPhoneFilter) {
            $aClients->andWhere(['like', 'phone', $this->sSearchPhoneFilter]);
        }

        $aClients->limit($this->onPage)->offset($this->iPageNum * $this->onPage);

        $iCount = Users::find()->where(['group_policy_id' => 3])
            ->count();

        $this->render(
            new view\Index([
                'items' => $aClients->all(),
                'onPage' => $this->onPage,
                'total' => $iCount,
                'page' => $this->iPageNum,
                'iStatusFilter' => $this->iStatusFilter,
                'sSearchNameFilter' => $this->sSearchNameFilter,
                'sSearchEmailFilter' => $this->sSearchEmailFilter,
                'sSearchPhoneFilter' => $this->sSearchPhoneFilter,
            ])
        );
    }

    /**
     * Редактируем письма активации.
     */
    protected function actionSaveMail()
    {
        $aData = $this->getInData();

        $sLanguage = $this->getInnerData('languageFilter');
        $this->setInnerData('languageFilter', '');

        if ($sLanguage) {
            foreach ($aData as $sName => $sValue) {
                if (in_array($sName, $this->aSettingsKeys)) {
                    //запись данных в таблицу параметров для модуля
                    ModulesParams::setParams('auth', $sName, $sLanguage, $sValue);
                } elseif (in_array($sName, $this->aSettingsKeysPass)) {
                    //запись данных в таблицу с языковыми метками
                    $oRow = Messages::getByName('auth', $sName, $sLanguage);
                    $oRow->value = $sValue;
                    $oRow->save();
                }
            }
        }

        $this->actionInit();
    }

    protected function actionShowMail()
    {
        $aModulesData = ModulesParams::getByModule('auth', $this->sLanguageFilter);
        $this->setInnerData('languageFilter', $this->sLanguageFilter);

        $aItems = [];
        $aItems['info'] = \Yii::t('auth', 'head_mail_text', [\Yii::t('app', 'site_label'), \Yii::t('app', 'url_label')]);

        foreach ($this->aSettingsKeys as  $key) {
            $aItems[$key] = (isset($aModulesData[$key])) ? $aModulesData[$key] : '';
        }

        foreach ($this->aSettingsKeysPass as $keyPass) {
            $aItems[$keyPass] = \Yii::t('data/auth', $keyPass);
        }

        $aLanguages = Languages::getAllActive();
        $aLanguages = ArrayHelper::map($aLanguages, 'name', 'title');

        $this->render(new view\Letters([
            'items' => $aItems,
            'lang' => $this->sLanguageFilter,
            'langList' => $aLanguages,
        ]));
    }

    protected function actionDelete()
    {
        $aData = $this->get('data');
        if (isset($aData['id'])) {
            $iItemId = $aData['id'];
            Users::deleteAll(['id' => $iItemId, 'group_policy_id' => 3]);
            Policy::incPolicyVersion();
        }
        $this->actionList();
    }

    protected function actionEditUser()
    {
        $iUserId = $this->getInDataValInt('id');

        $oUser = Users::findOne(['id' => $iUserId]);
        $this->showForm($oUser);
    }

    /**
     * Форма добавления.
     */
    protected function actionNewUser()
    {
        $this->showForm();
    }

    /**
     * @throws \Exception
     * @throws ui\ARSaveException
     */
    protected function actionSaveUser()
    {
        // получим данные
        $id = $this->getInDataValInt('id');

        $oUser = $id ? Users::findOne(['id' => $id]) : new Users();

        $oUser->name = $this->getInDataVal('name');
        $oUser->postcode = $this->getInDataVal('postcode');
        $oUser->address = $this->getInDataVal('address');
        $oUser->phone = $this->getInDataVal('phone');
        $oUser->user_info = $this->getInDataVal('user_info');

        // редактирование
        if ($id) {
            if ($oUser->validate()) {
                $oUser->save();
            } else {
                throw new ui\ARSaveException($oUser);
            }
        } else {
            $sPassword = $this->getInDataVal('pass');
            $oUser->pass = $sPassword;

            $sLogin = mb_strtolower($this->getInDataVal('login'));
            $oUser->login = $sLogin;

            $oUser->active = 1;

            if ($oUser->validate()) {
                $oUser->pass = Auth::buildPassword($sLogin, $sPassword);
                $oUser->save();
            } else {
                throw new ui\ARSaveException($oUser);
            }
        }

        $this->actionList();
    }

    protected function actionPass()
    {
        $aData['id'] = $this->getInDataValInt('id');

        $this->render(new view\Pass([
            'aData' => $aData,
        ]));
    }

    protected function actionSavePass()
    {
        $id = $this->getInDataVal('id');
        $sPass = $this->getInDataVal('pass');
        $sWpass = $this->getInDataVal('wpass');

        /**
         * @var Users
         */
        $oUser = Users::findOne(['id' => $id]);

        if ($oUser and ($sPass != '') and ($sPass == $sWpass)) {
            // проверка сложности пароля
            if (mb_strlen($sPass) < 6) {
                throw new UserException(\Yii::t('auth', 'err_short_pass'));
            }
            $oUser->pass = Auth::buildPassword($oUser->login, $sPass);
            $oUser->save();

            $this->actionInit();
        } else {
            $this->addError(\Yii::t('auth', 'error_pass_fields'));
        }
    }

    /**
     * Отображение формы добавления/редактирования формы.
     *
     * @param Users $oItem
     */
    private function showForm($oItem = null)
    {
        $aActiveStatusList = Api::getStatusList();

        $aData = [];
        if ($oItem && $oItem->getAttributes()) {
            $aData = $oItem->getAttributes();
        }

        if ($oItem) {
            $aData['active'] = $aActiveStatusList[$oItem->active];
        }
        $aData['pass'] = '';

        $bNotIsCurrentSysUser = $oItem && !UsersAuth::isCurrentSystemUser($oItem->id) ?: false;

        $this->render(new view\Form([
            'oItem' => $oItem,
            'aData' => $aData,
            'bNotIsCurrentSysUser' => $bNotIsCurrentSysUser,
            'notSocialNetworkUser' => $oItem && $oItem->network ? false : true,
        ]));
    }
}
