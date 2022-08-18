<?php

namespace skewer\components\auth;

use skewer\base\orm\Query;
use skewer\base\ui\ARSaveException;
use skewer\components\auth\models\Users as UsersModel;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property int $global_id
 * @property string $login
 * @property string $pass
 * @property int $group_policy_id
 * @property int $active
 * @property string $reg_date
 * @property string $lastlogin
 * @property string $name
 * @property string $email
 * @property string $postcode
 * @property string $address
 * @property string $phone
 * @property string $cache
 * @property int $version
 * @property int $del_block
 * @property string $user_info
 */
class Users
{
    public static function defaultLogin()
    {
        return 'default';
    }

    /**
     * @static Получаем информацию о пользователе по его ID
     *
     * @param $iUserId
     * @param array $aFileds - набор требуемых полей
     *
     * @return array|bool
     */
    public static function getUserData($iUserId, $aFileds = [])
    {
        if (
            $users = models\Users::find()
                ->select($aFileds)
                ->where(['id' => $iUserId])
                ->asArray()
                ->all()
        ) {
            return $users[0];
        }

        return false;
    }

    /** Метод для получения персональной информации о пользователе
     * @param $iUserId
     *
     * @return array|bool
     */
    public static function getUserDataOrDefault($iUserId)
    {
        /*
         * Если переданный ID пользователя больше 0, то считывается информация конкретного пользователя по его ID
         * Если ID равен нулю, меньше его или не был передан - считывается инфо дефолтного пользователя
         */
        if ($iUserId) {
            $aUserData = self::getUserData($iUserId);
        } else {
            $aUserData = self::getDefaultUserData();
        }

        return $aUserData;
    }

    public static function updateLoginTime($iUserId)
    {
        if ($user = models\Users::findOne(['id' => $iUserId])) {
            $user->lastlogin = date('Y-m-d H:i:s');

            return $user->save();
        }

        return false;
    }

    public static function getUserDataByLogin($sUserName)
    {
        return ($user = models\Users::findOne(['login' => mb_strtolower($sUserName)]))
            ? $user->getAttributes() : false;
    }

    /**
     * @static Метод для получения информации из таблицы для дефолтного пользователя по логину default
     *
     * @return array|bool
     */
    public static function getDefaultUserData()
    {
        return self::getUserDataByLogin(self::defaultLogin());
    }

    public static function delUser($iUserId)
    {
        return models\Users::deleteAll(['id' => $iUserId]);
    }

    /**
     * Взять id пользователя по логину.
     *
     * @static
     *
     * @param string $sLogin
     *
     * @return int
     */
    public static function getIdByLogin($sLogin)
    {
        return ($aUser = self::getUserDataByLogin($sLogin)) ? (int) $aUser['id'] : 0;
    }

    public static function getUserDetail($iUserId)
    {
        $aRow = Query::SelectFrom('group_policy')
            ->join('right', 'users', 'u', 'u.group_policy_id = group_policy.id')
            ->where('u.id', $iUserId)
            ->getOne();

        return $aRow;
    }

    public static function updUser($data)
    {
        if (!isset($data['id']) or !$user = models\Users::findOne(['id' => $data['id']])) {
            $user = new models\Users();
        }

        $user->setAttributes($data);
        if ($user->save()) {
            Policy::incPolicyVersion();

            return true;
        }
        throw new ARSaveException($user);

        return false;
    }

    /**
     * Отдает шаблонный набор значений для добавления новой записи.
     *
     * @return array
     */
    public static function getBlankValues()
    {
        return [
            'name' => \Yii::t('auth', 'new_user'),
            'active' => 1,
            'group_policy_id' => '',
        ];
    }

    /**
     * Проверяет, есть ли доступ к заданному пользователю у текущего.
     *
     * @static
     *
     * @param int $iUserId - id целевого пользователя
     *
     * @return bool
     */
    public static function hasAccessToUser($iUserId)
    {
        // взять запись
        $aUser = Users::getUserData($iUserId);

        // нет записи - нет и прав
        if (!$aUser) {
            return false;
        }

        // должен быть pass
        if (!$aUser['pass']) {
            return false;
        }

        // не административная политика
        if (CurrentAdmin::isLimitedRights()) {
            return false;
        }

        // попытка редактирования пользователя с высшим уровнем доступа
        if (!isset($aUser['group_policy_id']) || !Policy::hasAccessToPolicy($aUser['group_policy_id'])) {
            return false;
        }

        // прошел проверки, значит можно
        return true;
    }

    /**
     * Проверяет доступ к записи и, если нету, выбрасывает исключение.
     *
     * @static
     *
     * @param $iUserId
     *
     * @throws \Exception
     */
    public static function testAccessToUser($iUserId)
    {
        // системному админу можно все
        if (CurrentAdmin::isSystemMode()) {
            return;
        }
        if (!Users::hasAccessToUser($iUserId)) {
            throw new \Exception(\Yii::t('auth', 'no_access_to_record'));
        }
    }

    /**
     * Проверяет, является ли заданный id пользователя собственным и при этом системным
     *
     * @static
     *
     * @param int $iUserId - id проверяемого пользователя
     *
     * @return bool
     */
    public static function isCurrentSystemUser($iUserId)
    {
        return self::isCurrentUser($iUserId) and CurrentAdmin::isSystemMode();
    }

    /** Проверяет, является ли заданный id пользователя собственным
     * @static
     *
     * @param int $iUserId - id проверяемого пользователя
     *
     * @return bool
     */
    public static function isCurrentUser($iUserId)
    {
        return (int) $iUserId === (int) CurrentAdmin::getId();
    }

    /**
     * Проверяет, свободен ли логин.
     *
     * @static
     *
     * @param string $sLogin - проверяемое значение имени пользователя
     *
     * @return bool
     */
    public static function loginIsFree($sLogin)
    {
        // свободно, если такого пользователя нет
        return !(bool) Users::getIdByLogin($sLogin);
    }

    public static function isUsersFromSocialNetwork($email)
    {
        $user = UsersModel::find()
            ->select(['network'])
            ->where(['email' => $email])
            ->one();

        return ($user instanceof UsersModel && $user->network) ? true : false;
    }

    public static function getSocialNetworkByUser($email)
    {
        $user = UsersModel::find()
            ->select(['network'])
            ->where(['email' => $email])
            ->one();

        return $user instanceof UsersModel ? $user->network : null;
    }
}
