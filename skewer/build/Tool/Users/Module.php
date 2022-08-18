<?php

namespace skewer\build\Tool\Users;

use skewer\base\ui;
use skewer\build\Tool;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\auth\models\Users as UsersModel;
use skewer\components\auth\Policy;
use skewer\components\auth\Users;
use skewer\helpers\Validator;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class Module extends Tool\LeftList\ModulePrototype
{
    // число элементов на страницу
    protected $iOnPage = 20;

    // фильтр по политике
    protected $mPolicyFilter = false;

    // фильтр по активности пользователей
    protected $mActiveFilter = false;

    // фильтр по тексту
    protected $sSearchFilter = '';

    // текущий номер страницы
    protected $iPage = 0;

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page');

        // фильтры
        $this->mPolicyFilter = $this->get('policy', false);
        $this->mActiveFilter = $this->get('active', false);
        $this->sSearchFilter = $this->getStr('search');
    }

    /**
     * Первичное состояние.
     */
    protected function actionInit()
    {
        // вывод списка
        $this->actionList();
    }

    /**
     * Список пользователей.
     */
    protected function actionList()
    {
        $sSortColumn = $this->getInDataVal('sort_column', 'id');
        $sSortPosition = $this->getInDataVal('sort_position', 'DESC');

        /* Готовим данные */

        $oQuery = UsersModel::find();

        $oGroupPolicy = GroupPolicy::findOne(['alias' => 'sysadmin']);

        if (!CurrentAdmin::isSystemMode()) {
            $oQuery->andWhere(['!=', 'group_policy_id', $oGroupPolicy->id]);
        }
        if ($this->mActiveFilter !== false) {
            $oQuery->andWhere(['active' => $this->mActiveFilter]);
        }
        if ($this->mPolicyFilter !== false) {
            $oQuery->andWhere(['group_policy_id' => $this->mPolicyFilter]);
        }

        if ($this->sSearchFilter) {
            $oQuery->andWhere(
                [
                'or',
                ['like', 'name', $this->sSearchFilter],
                ['like', 'login', $this->sSearchFilter],
                ['like', 'email', $this->sSearchFilter],
            ]
        );
        }

        $totalCount = $oQuery->count();

        $aUsers = $oQuery
            ->orderBy([$sSortColumn => ($sSortPosition == 'ASC') ? SORT_ASC : SORT_DESC])
            ->offset($this->iPage * $this->iOnPage)
            ->limit($this->iOnPage)
            ->asArray()
            ->all();

        $policyList = Policy::getPolicyTitleList();

        foreach ($aUsers as &$paUser) {
            // переводим id политик в названия
            if (isset($policyList[$paUser['group_policy_id']])) {
                $paUser['group_policy_id'] = $policyList[$paUser['group_policy_id']];
            } else {
                $paUser['group_policy_id'] = \Yii::t('auth', 'no_policy');
            }
            // дата последнего захода
            if ($paUser['lastlogin'] <= 1900) {
                $paUser['lastlogin'] = '-';
            }
        }

        /* Cтроим интерфейс */

        $this->setPanelName(\Yii::t('auth', 'userList'));

        $this->render(new Tool\Users\view\Index([
            'sSearchFilter' => $this->sSearchFilter,
            'mPolicyFilter' => $this->mPolicyFilter,
            'aAllowedPolicyList' => Policy::getAllowedPolicyList(),
            'mActiveFilter' => $this->mActiveFilter,
            'aUsers' => $aUsers,
            'iOnPage' => $this->iOnPage,
            'iPage' => $this->iPage,
            'iTotalCount' => $totalCount,
            'sSortColumn' => $sSortColumn,
            'sSortPosition' => $sSortPosition,
        ]));
    }

    /**
     * Отображение формы.
     *
     * @throws UserException
     */
    protected function actionShow()
    {
        /******************
         * готовим данные
         */

        // взять id ( 0 - добавление, иначе сохранение )
        $iItemId = (int) $this->getInDataVal('id');

        // есть id - должны быть и права на доступ
        $iItemId and Users::testAccessToUser($iItemId); // иначе выход по exception

        // читаем запись или заводим заготовку для новой
        $aItem = $iItemId ? Users::getUserDetail($iItemId) : Users::getBlankValues();

        // если нет требуемой записи
        if ($iItemId and !$aItem) {
            throw new UserException(\Yii::t('auth', 'item_not_exists'));
        }
        // запрещаем пользователю редактировать активность и удалять собственный профиль или
        // профиль публичного пользователя в целях предотвращения самоубийства и отказа публичной части сайта
        $bSuicidable = true;

        if ($iItemId == CurrentAdmin::getId()) {
            $bSuicidable = false;
        }

        if ($aDefaultUser = Users::getDefaultUserData()) {
            if ($iItemId == $aDefaultUser['id']) {
                $bSuicidable = false;
            }
        }

        /********************
         * строим интерфейс
         */

        // заголовок - редактирование или добавление новой
        $this->setPanelName($iItemId ? \Yii::t('auth', 'editing') : \Yii::t('auth', 'adding'));

        $this->addModuleNoticeReport(\Yii::t('auth', 'user_read'), $aItem);

        $this->render(new Tool\Users\view\Show([
            'iItemId' => $iItemId,
            'isSocialNetworkUser' => ArrayHelper::getValue($aItem, 'network'),
            'aAllowedPolicyList' => Policy::getAllowedPolicyList(),
            'bSuicidable' => $bSuicidable,
            'aItem' => $aItem,
            'bNotCurrentSystemUser' => !Users::isCurrentSystemUser($iItemId),
        ]));
    }

    /**
     * Отображение формы смены пароля.
     *
     * @throws UserException
     */
    protected function actionPass()
    {
        // номер записи
        $iItemId = (int) $this->getInDataVal('id');

        // id - обязательное поле
        if (!$iItemId) {
            throw new UserException('нет id');
        }
        // текущему системному пользователю нельзя изменять логин и пароль
        if (Users::isCurrentSystemUser($iItemId)) {
            throw new UserException(\Yii::t('auth', 'current_user_changing_error'));
        }
        // запись пользователя
        $aItem = Users::getUserData($iItemId, ['id', 'login']);

        if (!$aItem) {
            throw new UserException(\Yii::t('auth', 'item_not_exists'));
        }
        // должны быть права на доступ
        Users::testAccessToUser($iItemId);

        // заголовок панели
        $this->setPanelName(\Yii::t('auth', 'password_changing'));

        $this->render(new Tool\Users\view\Pass([
            'aItem' => $aItem,
        ]));
    }

    /**
     * Сохранение данных пользователя.
     *
     * @throws UserException
     */
    protected function actionSave()
    {
        // номер записи
        $iItemId = (int) $this->getInDataVal('id');

        // поля для записи
        $aFields = $iItemId ? UsersModel::updFieldsFilter() : UsersModel::insertFieldsFilter();

        // взять данные
        $aData = $this->getInData($aFields);

        if (!$aData) {
            throw new UserException(\Yii::t('auth', 'no_data_for_saving'));
        }
        // если добавление
        if (!$iItemId) {
            // проверить заданность login и pass
            $sLogin = mb_strtolower($aData['login']);
            $sPass = $aData['pass'];

            if (!Validator::isLogin($sLogin)) {
                throw new UserException(\Yii::t('auth', 'invalid_login'));
            }
            // проверка доступности логина
            if (!Users::loginIsFree($sLogin)) {
                throw new UserException(\Yii::t('auth', 'login_exists'));
            }
            if (!$sPass) {
                throw new UserException(\Yii::t('auth', 'password_expected'));
            }
            // проверить соответствие поддтверждения пароля
            if ($aData['pass'] !== $aData['pass2']) {
                throw new UserException(\Yii::t('auth', 'passwords_not_match'));
            }
            // проверка сложности пароля
            if (mb_strlen($sPass) < 6) {
                throw new UserException(\Yii::t('auth', 'err_short_pass'));
            }
            // проверить задание политики доступа
            if (!$aData['group_policy_id']) {
                throw new UserException(\Yii::t('auth', 'group_policy_id_exptected'));
            }
            Policy::testAccessToPolicy($aData['group_policy_id']);

            $aData['pass'] = Auth::buildPassword($sLogin, $sPass);
        } else {
            // обновление

            if (!isset($aData['group_policy_id'])) {
                throw new UserException(\Yii::t('auth', 'group_policy_id_exptected'));
            }
            if (!$aData['group_policy_id']) {
                unset($aData['group_policy_id']);
            } // это чтобы не сохранить 0 вместо значения
            // должны быть права на доступ
            //\skewer\build\Tool\Users\Api::testAccessToPolicy( $aData['group_policy_id'] ); // не приходит значение политики, тк элемент неактивен // проверка пришедшей политики, а не сохраненной!
            Users::testAccessToUser($iItemId);

            // с себя активность снять нельзя
            if (Users::isCurrentUser($iItemId)) {
                unset($aData['active']);
            }

            // с default активность нельзя снять
            $aCurUserData = Users::getUserDetail($iItemId);
            if ($aCurUserData['login'] == 'default') {
                unset($aData['active']);
            }
        }

        // есть данные - сохранить
        $bRes = Users::updUser($aData);

        if ($iItemId) {
            if ($bRes) {
                $this->addMessage(\Yii::t('auth', 'data_saved'));
                $this->addModuleNoticeReport(\Yii::t('auth', 'user_editing'), $aData);
            } else {
                $this->addError(\Yii::t('auth', 'data_not_saved'));
            }
        } else {
            if ($bRes) {
                $this->addMessage(\Yii::t('auth', 'user_added'));
                unset($aData['id'], $aData['pass'], $aData['pass2']);

                $this->addModuleNoticeReport(\Yii::t('auth', 'user_creating'), $aData);
            } else {
                $this->addError(\Yii::t('auth', 'user_not_added'));
            }
        }

        // вывод списка
        $this->actionList();
    }

    /**
     * Сохранение пароля.
     *
     * @throws UserException
     */
    protected function actionSavePass()
    {
        // запросить данные
        $aData = $this->get('data');
        if (!is_array($aData)) {
            throw new UserException('wrong input data');
        }
        // взять данные
        $iId = (int) ($aData['id'] ?? 0);
        $sPass1 = (string) ($aData['pass'] ?? '');
        $sPass2 = (string) ($aData['pass2'] ?? '');

        // проверка наличия полей
        if (!$iId) {
            throw new UserException('no `id`');
        }
        // текущему системному пользователю нельзя изменять логин и пароль
        if (Users::isCurrentSystemUser($iId)) {
            throw new UserException(\Yii::t('auth', 'current_user_changing_error'));
        }
        // проверка прав на доступ
        Users::testAccessToUser($iId);

        // пароль - обязательное поле
        if (!$sPass1) {
            throw new UserException(\Yii::t('auth', 'password_expected'));
        }
        // проверка правильности пароля
        if ($sPass1 !== $sPass2) {
            throw new UserException(\Yii::t('auth', 'passwords_not_match'));
        }
        // проверка сложности пароля
        if (mb_strlen($sPass1) < 6) {
            throw new UserException(\Yii::t('auth', 'err_short_pass'));
        }
        if ($user = UsersModel::findOne(['id' => $iId])) {
            /* @var UsersModel $user */
            $user->pass = Auth::buildPassword($user->login, $sPass1);

            if ($user->save()) {
                $this->addMessage(\Yii::t('auth', 'password_saved'));
                $user->pass = '******';
                $this->addModuleNoticeReport(\Yii::t('auth', 'password_editing'), $user->getAttributes());
            } else {
                $this->addError(\Yii::t('auth', 'password_not_changing'));
            }
        }

        // вывод списка
        $this->actionShow();
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        // запросить данные
        $aData = $this->get('data');

        // id записи
        $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

        // проверка прав на доступ
        Users::testAccessToUser($iItemId);

        // запросить данные пользователя
        $aUser = Users::getUserData($iItemId, ['id', 'login', 'name', 'email']);

        // удаление
        $bRes = Users::delUser($iItemId);

        if ($bRes) {
            Policy::incPolicyVersion();
            $this->addMessage(\Yii::t('auth', 'user_deleted'));
            $this->addModuleNoticeReport(\Yii::t('auth', 'user_deleting'), $aUser);
        } else {
            $this->addError(\Yii::t('auth', 'user_not_deleted'));
        }

        // вывод списка
        $this->actionList();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'policy' => $this->mPolicyFilter,
            'active' => $this->mActiveFilter,
            'page' => $this->iPage,
        ]);
    }
}
