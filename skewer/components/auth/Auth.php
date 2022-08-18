<?php

namespace skewer\components\auth;

use Exception;
use skewer\base\log\Logger;
use skewer\base\orm\Query;
use skewer\base\site\Site;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\design\Design;
use skewer\components\gateway;
use skewer\components\modifications\GetModificationEvent;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с авторизационными механизмами.
 */
class Auth
{
    const AFTER_LOGIN = 'afterLogin';

    // Секретное слово для генерации пароля
    private static $sSalt = 'Canape3.0';

    /**
     * @var null|firewall\Firewall
     */
    public static $oFirewall = null;

    /**
     * id группы неавторизованного пользователя.
     *
     * @return null|mixed
     */
    public static function getDefaultGroupId()
    {
        return \Yii::$app->getParam(['auth', 'public_default_id']);
    }

    public static function init()
    {
        /*
         *  Если IP в сессии пользователя не совпадает с пришедшим - разлогиневаем текущего
         * публичного и админа
         */
        /* Мы уже залогонились и имеем IP  */
        $ip = ArrayHelper::getValue($_SERVER, 'HTTP_X_FORWARDED_FOR', \Yii::$app->request->getUserIP());
        if (isset($_SESSION['auth']['userIP'])) {
            if ($_SESSION['auth']['userIP'] != $ip) {
                self::logout('public');
                self::logout('admin');
            }
        }

        /* Отлавливаем попытку подмены куки  */
        if (isset($_SESSION['auth']['hostName'])) {
            $host = $_SESSION['auth']['hostName'];
            // второе условие для работы дебаг режима админки. обычно localhost:8000 или :8002
            if (!strstr($_SERVER['HTTP_HOST'], $host) and !preg_match('/^localhost:800\d$/', $host)) {
                self::logout('public');
                self::logout('admin');
            }
        }

        // Если пользователь авторизован и
        // возникла необходимость обновления политики доступа
        if ((self::getUserId('public')) and
             (self::getPolicyVersion('public') != Policy::getPolicyVersion())) {
            // то попробовать обновить политику текущего пользователя
            if (self::loadUser('public', self::getUserId('public'))) {
                self::reloadPolicy('public');
            } else {
                // иначе сбросить сессию забаненного или удалённого пользователя
                self::logout('public');
            }
        }

        // Если отсутствует сессия пользователя, то установить пользователя и политику по-умолчанию
        if (!self::getUserId('public')) {
            self::defaultPolicy();
        }

        if (self::getUserId('admin')) {
            if (self::getPolicyVersion('admin') != Policy::getPolicyVersion()) {
                self::loadUser('admin', self::getUserId('admin'));
                self::reloadPolicy('admin');
            }
        }
    }

    /**
     * Выбор дефолтной политики по фильтрам
     */
    private static function defaultPolicy()
    {
        $aUserData = Users::getDefaultUserData();
        $aUserData['group_policy_id'];

        self::$oFirewall = new firewall\Firewall();
        $iUserId = self::$oFirewall->getUserId(\Yii::$app->request->getUserIP(), $aUserData['group_policy_id'], $aUserData['id']);
        /* Если запрашиваемый пользователь не изменился - отключаем firewall */
        if ($iUserId == $aUserData['id']) {
            self::$oFirewall->enable(false);
        }

        self::loadUser('public', $iUserId);
    }

    public static function checkUserLocal($aInputData, $bAuto = false)
    {
        if (!$aInputData) {
            return false;
        }

        if ($bAuto) {
            $aRow = Query::SelectFrom('group_policy')
                ->join('inner', 'users', 'u', 'u.group_policy_id = group_policy.id')
                ->where(['login' => $aInputData['login'], 'users.active' => $aInputData['active'], 'gp.area' => $aInputData['login_area']])
                ->getOne();
        } elseif (isset($aInputData['network'])) {
            $aRow = Query::SelectFrom('group_policy')
                ->join('inner', 'users', 'u', 'u.group_policy_id = group_policy.id')
                ->where([
                    'login' => $aInputData['login'],
                    'area' => $aInputData['login_area'],
                    'pass' => $aInputData['password'],
                    'network' => $aInputData['network'],
                ])
                ->getOne();
        } else {
            $aRow = Query::SelectFrom('group_policy')
                ->join('inner', 'users', 'u', 'u.group_policy_id = group_policy.id')
                ->where(['login' => $aInputData['login'], 'area' => $aInputData['login_area'], 'pass' => $aInputData['password']])
                ->getOne();
        }

        if ($aRow) {
            unset($aRow['pass']);
        }

        return $aRow;
    }

    public static function loadPolicy($sLayer, $iPolicyId, /** @noinspection PhpUnusedParameterInspection */$iUserId = 0)
    {
        $aGroupPolicy = Policy::getPolicyHeader($iPolicyId);
        if (!$aGroupPolicy or !$aGroupPolicy['active']) {
            return false;
        }

        // Считываем список разрешенных для чтения разделов
        $aGroupPolicyData = Policy::getGroupPolicyData($iPolicyId);

        $_SESSION['auth'][$sLayer]['policy_version'] = (int) $aGroupPolicyData['version'];
        $_SESSION['auth'][$sLayer]['start_section'] = ((int) $aGroupPolicyData['start_section']) ? (int) $aGroupPolicyData['start_section'] : \Yii::$app->sections->main();
        $_SESSION['auth'][$sLayer]['read_access'] = $aGroupPolicyData['read_access'];
        $_SESSION['auth'][$sLayer]['actions_access'] = $aGroupPolicyData['actions_access'];
        $_SESSION['auth'][$sLayer]['modules_access'] = $aGroupPolicyData['modules_access'];

        if (count($aGroupPolicyData['read_disable'])) {
            $_SESSION['auth'][$sLayer]['read_disable'] = $aGroupPolicyData['read_disable'];
        }

        return true;
    }

    // func

    /**
     * Возвращает id стартового раздела текущей политики.
     *
     * @static
     *
     * @param $sLayer
     *
     * @return bool
     */
    public static function getMainSection($sLayer = 'public')
    {
        return (isset($_SESSION['auth'][$sLayer]['start_section'])) ? $_SESSION['auth'][$sLayer]['start_section'] : false;
    }

    // func

    public static function logout($sLayer = 'public')
    {
        switch ($sLayer) {
            case 'public':
                if (isset($_SESSION['auth'][$sLayer])) {
                    unset($_SESSION['auth'][$sLayer]);
                }
            break;

            case 'admin':
                if (isset($_SESSION['auth'][$sLayer])) {
                    unset($_SESSION['auth'][$sLayer]);
                }

                /*Сброс связки с КанапеИд*/
                \Yii::$app->session->set('current_canape_id_login', null);
                \Yii::$app->session->set('lastlogin', null);

                Design::unsetModeGlobalFlag(); // дополнительно, сбрасываем флаг дизайнерского режима, чтобы не палить js
            break;
        }

        return true;
    }

    /**
     * Проверяет пользователя $sLogin с паролем $sPassword через шлюз в системе управления сайтами
     * Проверка осуществляется по двум критериям:
     * 1. Пользователь должен существовать и иметь установленный флаг активности.
     * 2. Пользователь должен находиься в группе владельцев сайта с которого происходит запрос авторизации.
     *
     * @param string $sLogin Логин пользователя
     * @param string $sPassword Пароль пользователя
     *
     * @throws gateway\Exception
     *
     * @return bool Возвращает true если пользователь найден либо false в противном случае
     */
    protected static function checkGlobalUser($sLogin, $sPassword)
    {
        $bSuccess = false;

        /* Сайт не подключен к кластеру */
        if (!INCLUSTER) {
            /* Получаем список прописанных в конфиге пользователей */
            $aUsers = \Yii::$app->getParam('users');

            if (!$aUsers) {
                return false;
            }

            /* Обходим пользователей, если есть совпадение - разрешаем авторизацию */
            foreach ($aUsers as $aUser) {
                if ($aUser['login'] == $sLogin && $aUser['pass'] == $sPassword) {
                    return true;
                }
            }

            return false;
        }

        try {
            $oClient = gateway\Api::createClient();

            $aData['login'] = mb_strtolower($sLogin);
            $aData['password'] = $sPassword;

            $oClient->addMethod('HostTools', 'login', $aData, static function ($mResult, $mError) use (&$bSuccess) {
                /* Ошибок нет и авторизация подтверждена */
                if (!$mError && $mResult) {
                    $bSuccess = true;
                }
            });

            if (!$oClient->doRequest()) {
                throw new gateway\Exception($oClient->getError());
            }
        } catch (gateway\Exception $e) {
            Logger::dump('Global User Auth error: ' . $e->getMessage());
        }

        return $bSuccess;
    }

    // func

    /**
     * Метод проверки существования пользователя.
     * В случае успешной выборки метод возвращает id пользователя, прошедшего авторизацию.
     *
     * @param $sLayer
     * @param $sLogin
     * @param $sPassword
     *
     * @return array|bool Массив данных по пользователю либо false
     */
    public static function checkUser($sLayer, $sLogin, $sPassword)
    {
        $aFilter = [
            'login' => mb_strtolower($sLogin),
            'password' => self::buildPassword($sLogin, $sPassword),
            'login_area' => $sLayer,
            'active' => '1',
        ];

        // Выбрать пользователя
        $mUser = self::checkUserLocal($aFilter);

        if (!$mUser) {
            return false;
        }

        /* Проверяем через внешнюю авторизацию */
        if (($mUser['global_id'])) {
            return (self::checkGlobalUser($sLogin, $sPassword)) ? $mUser : false;
        }

        return $mUser;
    }

    // function checkUser()

    // Генерация хэша пароля по логину, паролю и секретному слову
    public static function buildPassword($sLogin, $sPassword)
    {
        $sLogin = mb_strtolower($sLogin);

        return md5($sLogin . $sPassword . self::$sSalt);
    }

    // function buildPassword()

    /**
     * Возвращает доступен ли пользователю раздел для чтения.
     *
     * @param $sLayer
     * @param int $iSectionId id раздела
     *
     * @return bool
     */
    public static function isReadable($sLayer, $iSectionId)
    {
        // Если запрошенный раздел есть в списке разрешенных - отдать true
        if (!isset($_SESSION['auth'][$sLayer])) {
            return false;
        }
        // !! не учнена политика системного администратора - обрабатывается вызывающим методом
        if (!$iSectionId || array_search($iSectionId, $_SESSION['auth'][$sLayer]['read_access']) !== false) {
            return true;
        }

        return false;
    }

    // func

    /**
     * Возвращает значение параметра функционального уровня политики доступа установленной
     * для пользователя (группа+персональная политика)D.
     *
     * @static
     *
     * @param $sLayer
     * @param string $moduleClassName имя класса модуля
     * @param string $paramName имя параметра модуля
     * @param mixed $defValue Значение по-умолчанию если параметра с таким именем нет
     *
     * @return mixed
     */
    public static function getModuleParam(/* @noinspection PhpUnusedParameterInspection */
        $sLayer,
        $moduleClassName,
        $paramName,
        $defValue = null
    ) {
        return false;

        /*if ( isset($this->aGroupPolicy['actions_access'][$sModuleClassName][$sParamName] ) ){

            return $this->aGroupPolicy['actions_access'][$sModuleClassName][$sParamName]['value'];
        }
        else return $mDefValue;*/
    }

    /**
     * Возвращает булевое значение параметра функционального уровня политики доступа установленной
     * для пользователя (группа+персональная политика).
     *
     * @static
     *
     * @param int $userId id пользователя
     * @param string $moduleClassName имя класса модуля
     * @param string $paramName имя параметра модуля
     * @param mixed $defValue Значение по-умолчанию если параметра с таким именем нет
     *
     * @return bool|mixed
     */
    public static function userCanDo(/* @noinspection PhpUnusedParameterInspection */
        $userId,
        $moduleClassName,
        $paramName,
        $defValue = null
    ) {
        return false;
    }

    /**
     * Метод авторизации пользователя.
     *
     * @param $sLayer
     * @param string $sLogin
     * @param string $sPassword
     *
     * @return bool
     */
    public static function login($sLayer, $sLogin = '', $sPassword = '')
    {
        /**
         * Проверить доступ к политике по фильтрам
         */
        $aUser = self::checkUser($sLayer, $sLogin, $sPassword);

        /* Пользователь не существует либо параметры не верны */
        if (!$aUser) {
            return false;
        }

        $bRes = self::loadUser($sLayer, $aUser['id']);

        if ($bRes) {
            // Проверяем пользователя на существование и на активность
            $aUserData = Users::getUserDataOrDefault($aUser['id']);

            /*Переложим данные последнего захода в сессию*/
            \Yii::$app->session->set('lastlogin', $aUserData['lastlogin']);

            // обновляем дату последнего захода
            Users::updateLoginTime($aUser['id']);

            \Yii::$app->trigger(self::AFTER_LOGIN);
        }

        return $bRes;
    }

    // function login()

    /**
     * Для корректной авторизации пользователя
     *  пароль строится на основе uid + network.
     *
     * @param $layer
     * @param $login
     * @param $password
     * @param string $network
     *
     * @throws Exception
     * @throws gateway\Exception
     *
     * @return array|bool|\skewer\base\orm\ActiveRecord
     */
    public static function loginNetwork($layer, $login, $password, $network = '')
    {
        // Выбрать пользователя
        $mUser = self::checkUserLocal([
            'login' => mb_strtolower($login),
            'password' => self::buildPassword($network, $password),
            'network' => $network,
            'login_area' => $layer,
            'active' => '1',
        ]);

        if (!$mUser) {
            return false;
        }

        /* Проверяем через внешнюю авторизацию */
        if (($mUser['global_id'])) {
            return self::checkGlobalUser($login, $password)
                ? $mUser
                : false;
        }

        /* Пользователь не существует либо параметры не верны */
        if (!$mUser) {
            return false;
        }

        $bRes = self::loadUser($layer, $mUser['id']);

        if ($bRes) {
            // Проверяем пользователя на существование и на активность
            $aUserData = Users::getUserDataOrDefault($mUser['id']);

            /*Переложим данные последнего захода в сессию*/
            \Yii::$app->session->set('lastlogin', $aUserData['lastlogin']);

            // обновляем дату последнего захода
            Users::updateLoginTime($mUser['id']);

            \Yii::$app->trigger(self::AFTER_LOGIN);
        }

        return $bRes;
    }

    // function login()

    /**
     * @param $sLayer
     * @param int $iUserId
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function loadUser($sLayer, $iUserId = 0)
    {
        try {
            // Проверяем пользователя на существование и на активность
            $aUserData = Users::getUserDataOrDefault($iUserId);

            $iStatusActive = (int) $aUserData['active'];

            if (!$aUserData or $iStatusActive != 1) {
                throw new Exception('User not found or not active');
            }
            // Загружаем персональную информацию о пользователе по его ID
            $_SESSION['auth'][$sLayer]['userData'] = $aUserData;
            $iUserId = $_SESSION['auth'][$sLayer]['userData']['id'];
            $iPolicyId = (int) $_SESSION['auth'][$sLayer]['userData']['group_policy_id'];
            $aPolicyHeader = Policy::getPolicyHeader($iPolicyId);

            /* Проверка на системного пользователя */
            if (CurrentAdmin::getLogin() == 'sys' and
                $sLayer == 'admin' and
                isset($aPolicyHeader['alias']) and
                $aPolicyHeader['alias'] == 'sysadmin'
            ) {
                /* Пользователь системный - взводим флаг */
                $_SESSION['auth'][$sLayer]['userData']['systemMode'] = true;
            } else {
                $_SESSION['auth'][$sLayer]['userData']['systemMode'] = false;
            }

            // если это sys пользователь и включен режим "обычный администратор", то выбирается стандартная политика
            if ($sLayer == 'admin' and CurrentAdmin::isSystemModeByUserData() and CurrentAdmin::isTempAdminMode()) {
                /** @var GroupPolicy $policyData */
                $policyData = GroupPolicy::find()
                    ->select(['id'])
                    ->where(['alias' => 'admin'])
                    ->one()
                ;
                if (!$policyData) {
                    throw new \Exception('admin policy not found');
                }
                $iPolicyId = (int)$policyData->id;
                $aPolicyHeader = Policy::getPolicyHeader($iPolicyId);
                $_SESSION['auth'][$sLayer]['userData']['group_policy_id'] = $iPolicyId;
            }

            if (!(self::$oFirewall instanceof firewall\Firewall)) {
                self::$oFirewall = new firewall\Firewall();
            }

            if (!self::$oFirewall->checkAccess(\Yii::$app->request->getUserIP(), $iPolicyId)) {
                throw new Exception('Firewall blocked access!');
            }
            /* Сохраняем в сессию IP пользователя, под которым логинились */
            $_SESSION['auth']['userIP'] = ArrayHelper::getValue($_SERVER, 'HTTP_X_FORWARDED_FOR', \Yii::$app->request->getUserIP());

            // Сохраняем в сессию host_name для пользователя
            $_SESSION['auth']['hostName'] = Site::domain();

            if (!$iPolicyId) {
                throw new Exception('No policy');
            }
            $bPolicy = self::loadPolicy($sLayer, $iPolicyId, $iUserId);
            if (!$bPolicy) {
                throw new Exception('Policy not found or not active');
            }
            /* Получаем данные по политике */
            $_SESSION['auth'][$sLayer]['userData']['policyAlias'] = $aPolicyHeader['alias'];
            $_SESSION['auth'][$sLayer]['userData']['access_level'] = $aPolicyHeader['access_level'];

            return true;
        } catch (Exception $e) {
            //авторизовать как дефолтного
            self::logout($sLayer);
            self::defaultPolicy();

            return false;
        }
    }

    // func

    public static function reloadPolicy($sLayer)
    {
        if (!$iPolicyId = self::getPolicyId($sLayer)) {
            return false;
        }
        Policy::checkCache($iPolicyId);
        self::loadPolicy($sLayer, $iPolicyId);

        return true;
    }

    // func

    public static function getUserId($sLayer)
    {
        return (!isset($_SESSION['auth'][$sLayer]['userData']['id'])) ? false : $_SESSION['auth'][$sLayer]['userData']['id'];
    }

    // func

    public static function getPolicyId($sLayer)
    {
        return (!isset($_SESSION['auth'][$sLayer]['userData']['group_policy_id'])) ? false : $_SESSION['auth'][$sLayer]['userData']['group_policy_id'];
    }

    // func

    public static function getAccessLevel($sLayer)
    {
        return (!isset($_SESSION['auth'][$sLayer]['userData']['access_level'])) ? 1000 : $_SESSION['auth'][$sLayer]['userData']['access_level'];
    }

    // func

    public static function getPolicyVersion($sLayer)
    {
        return (!isset($_SESSION['auth'][$sLayer]['policy_version'])) ? false : $_SESSION['auth'][$sLayer]['policy_version'];
    }

    // func

    public static function isLoggedIn($sLayer)
    {
        return (bool) self::getUserId($sLayer);
    }

    // func

    public static function getUserData($sLayer)
    {
        return (isset($_SESSION['auth'][$sLayer]['userData'])) ? $_SESSION['auth'][$sLayer]['userData'] : false;
    }

    // func

    public static function getReadableSections($sLayer)
    {
        return (isset($_SESSION['auth'][$sLayer]['read_access'])) ? $_SESSION['auth'][$sLayer]['read_access'] : false;
    }

    // func

    /**
     * @param $sLayer
     *
     * @return array|bool
     */
    public static function getDenySections($sLayer)
    {
        return (isset($_SESSION['auth'][$sLayer]['read_disable'])) ? $_SESSION['auth'][$sLayer]['read_disable'] : false;
    }

    // func

    /**
     * Возвращает список запрещенных к чтению разделов для пользователя $iUserId. Если $iUserId не указан
     * то возвращает список для default пользователя.
     *
     * @param bool $iUserId
     *
     * @throws Exception В случае нарушения целостности генерирует исключение
     *
     * @return array Возвращает массив id разделов либо пустой массив
     */
    public static function getDenySectionByUserId($iUserId = false)
    {
        $aUserData = Users::getUserDataOrDefault($iUserId);

        if (!$aUserData['group_policy_id']) {
            throw new \Exception('Wanted user does not exists!');
        }
        $aGroupPolicyData = Policy::getGroupPolicyData($aUserData['group_policy_id']);
        if (!count($aGroupPolicyData)) {
            throw new \Exception('The policy does not have a permissions cache!');
        }
        if (count($aGroupPolicyData['read_disable'])) {
            $aGroupPolicyData['read_disable'];
        }

        return $aGroupPolicyData['read_disable'];
    }

    public static function getAvailableModules($sLayer)
    {
        return (isset($_SESSION['auth'][$sLayer]['modules_access'])) ? $_SESSION['auth'][$sLayer]['modules_access'] : false;
    }

    public static function canDo($sLayer, $moduleClassName, $paramName, $defValue = false)
    {
        return (isset($_SESSION['auth'][$sLayer]['actions_access'][$moduleClassName][$paramName])) ? $_SESSION['auth'][$sLayer]['actions_access'][$moduleClassName][$paramName]['value'] : $defValue;
    }

    public static function canUsedModule($sLayer, $moduleClassName)
    {
        return isset($_SESSION['auth'][$sLayer]['modules_access'][$moduleClassName]);
    }

    public static function authUserByToken($sToken)
    {
        if (!INCLUSTER) {
            return;
        }

        $oClient = gateway\Api::createClient();

        $aParam = [$sToken];

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'getAuthAdmin', $aParam, static function ($mResult, $mError) {
            if ($mResult) {
                $aResult = explode(':', $mResult);

                if (isset($aResult[1]) && $aResult[1] . '/' == WEBROOTPATH) {
                    $aUserData = Users::getUserDataByLogin($aResult[0]);

                    if (!$aUserData) {
                        $aUserData = Users::getUserDataByLogin('admin');
                    }

                    if ($iUserId = $aUserData['id']) {
                        /* Убрать после выяснения вопроса выше */
                        if (!(self::$oFirewall instanceof firewall\Firewall)) {
                            self::$oFirewall = new firewall\Firewall();
                        }

                        self::$oFirewall->enable(false);

                        self::loadUser('admin', $iUserId);

                        // обновляем дату последнего захода
                        Users::updateLoginTime($iUserId);
                    }
                }
            }
        });

        if (!$oClient->doRequest()) {
            throw new Exception($oClient->getError());
        }
    }

    public static function className()
    {
        return get_called_class();
    }

    public static function getLastMod(GetModificationEvent $event)
    {
        $event->setLastTime(self::getLastModification());
    }

    public static function getLastModification()
    {
        $aRow = \skewer\base\log\models\Log::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->where(['module' => 'Auth'])
            ->asArray()
            ->one();
        if (isset($aRow['event_time'])) {
            return strtotime($aRow['event_time']);
        }

        return 0;
    }
}
