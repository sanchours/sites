<?php

namespace skewer\components\auth;

use skewer\base\orm\Query;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\base\ui\ARSaveException;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\auth\models\GroupPolicyData;
use skewer\components\auth\models\GroupPolicyFunc;
use skewer\components\auth\models\GroupPolicyModule;
use skewer\components\config;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с политиками доступа.
 */
class Policy
{
    /** статус активности "Системный администратор сайта" */
    const stSys = -1;
    /** статус активности "Деактивирован" */
    const stInactive = 0;
    /** статус активности "Активирован" */
    const stActive = 1;

    /** политика доступа "Административная политика" */
    const POLICY_ADMIN_USERS = 1;

    /** политика доступа "Зарегистрированный пользователь" */
    const PUBLIC_USERS = 3;

    const DELIMITER_PARAMS_MODULE = '_';

    const DELIMITER_PARAMS = '_';

    /** текущая версия политик */
    protected static $currentVersion = null;

    /**
     * Проводит актуализации данных политики если необходимо.
     *
     * @param $iPolicyId
     *
     * @return bool
     */
    public static function checkCache($iPolicyId)
    {
        $policy = GroupPolicyData::find()
            ->select(['version'])
            ->where(['policy_id' => $iPolicyId])
            ->limit(1)
            ->all();

        // проверяем на актуальность версии текущей политики
        if (!$policy || $policy[0]->version < self::$currentVersion) {
            self::updateCache($iPolicyId);
        }

        return true;
    }

    /**
     * Обновить кеш политик доступа.
     *
     * @static
     *
     * @param int $iPolicyId id политики
     *
     * @return bool
     */
    public static function updateCache($iPolicyId)
    {
        // Получить массив разделов, доступных для чтения по ID политики
        $aSectionAccess = self::getAccessArray($iPolicyId);

        // Запись в БД сформированного кэша
        self::updateCacheByPolicyId($iPolicyId, $aSectionAccess, self::getPolicyVersion());

        return false;
    }

    /**
     * @static Метод получения массива разделов, доступных для чтения, для заданной политики доступа
     *
     * @param int $iPolicyId ID политики доступа
     *
     * @return array
     */
    public static function getAccessArray($iPolicyId)
    {
        $aAccessArray = [
            'read_access' => [],
        ];
        $aAccessTypes = [];

        // Прочитать из БД - какие корневые разделы разрешены для чтения
        $aTempAccessTypes = [];

        if (
            $iPolicyId and
            $policyData = GroupPolicyData::find()
                ->select(['read_disable', 'read_enable'])
                ->where(['policy_id' => $iPolicyId])
                ->limit(1)
                ->asArray()
                ->all()
        ) {
            $aTempAccessTypes = $policyData[0];
        }

        // Разбиваем прочитанные строки по запятой и пересобираем массив
        if ($aTempAccessTypes && count($aTempAccessTypes)) {
            foreach ($aTempAccessTypes as $sKey => $sValue) {
                $aTempSections = [];
                if ($sValue) {
                    $aTempSections = explode(',', $sValue);
                }

                $aAccessTypes[$sKey] = $aTempSections;
            }
        }

        // Вызаваем метод построения массива разделов, доступных для чтения
        self::getTree($aAccessArray, $aAccessTypes);

        return $aAccessArray;
    }

    /**
     * @static Рекурсивный метод построения массива разделов, доступных для чтения
     *
     * @param array $aSectionsArray Ссылка на результирующий массив
     * @param array $aAccessTypes Массив возможных типов доступа к разделу
     * @param int $iState Текущее состояние
     * @param int $iParentSection Текущий родитель
     *
     * @return bool
     */
    public static function getTree(&$aSectionsArray, $aAccessTypes, $iState = 0, $iParentSection = 0)
    {
        // Получаем массив ID разделов по текущему родителю
        $list = \skewer\base\section\Tree::getSectionByParent($iParentSection);

        // Проход по массиву полученных ID
        if ($list) {
            foreach ($list as $aRow) {
                // Устанавливаем текущий ID
                $iCurrentId = $aRow['id'];
                // Устанавливаем текущее состояние
                $iCurrentState = $iState;

                // Проходимся по каждому из типов доступа к разделу и содержащимся в нем id корневых разделов, для которых задается доступ
                foreach ($aAccessTypes as $sKey => $aTypeArray) {
                    // Если текущий ID содержится в массиве ID для рассматриваемого типа доступа - устанавливаем соответствующее текущее состояние
                    if (in_array($iCurrentId, $aTypeArray)) {
                        switch ($sKey) {
                            case 'read_disable':
                                $iCurrentState = 0;
                            break;
                            case 'read_enable':
                                $iCurrentState = 1;
                            break;
                    }
                    }
                }

                // Если текущее состояние 0 - ID не добавляется к списку разрешенных для чтения разделов, если больше 0 ( в данном случае 1 ) - добавляется
                if ($iCurrentState) {
                    $aSectionsArray['read_access'][] = $iCurrentId;
                }

                // Рекурсивный вызов метода с установленным текущим состоянием для всех подразделов текущего ID
                self::getTree($aSectionsArray, $aAccessTypes, $iCurrentState, $iCurrentId);
            }
        }

        return true;
    }

    // func

    /**
     * Возвращает массив параметров функционального уровня групповой политики.
     *
     * @static
     *
     * @param $iPolicyId
     *
     * @return array|bool
     */
    public static function getGroupActionData($iPolicyId)
    {
        $out = [];

        if ($actions = GroupPolicyFunc::find()
            ->select(['module_name', 'param_name', 'value'])
            ->where(['policy_id' => $iPolicyId])
            ->asArray()
            ->all()
        ) {
            // Пересобераем массив в нужном виде
            foreach ($actions as $action) {
                $out[$action['module_name']][$action['param_name']] =
                    [
                        'value' => $action['value'],
                    ];
            }
        }

        return $out;
    }

    /**
     * Установить значение параметра функционального уровня для группы.
     *
     * @static
     *
     * @param int $iPolicyId id политики
     * @param string $sModuleClassName имя класса модуля
     * @param string $sParamName имя параметра модуля
     * @param mixed $mValue значение
     *
     * @return bool
     */
    public static function setGroupActionParam($iPolicyId, $sModuleClassName, $sParamName, $mValue)
    {
        if (!$action = GroupPolicyFunc::findOne(
            [
                'policy_id' => $iPolicyId,
                'module_name' => $sModuleClassName,
                'param_name' => $sParamName,
            ]
        )
        ) {
            $action = new GroupPolicyFunc();
            $action->policy_id = $iPolicyId;
            $action->module_name = $sModuleClassName;
            $action->param_name = $sParamName;
        }

        $action->value = (string) $mValue;

        return $action->save();
    }

    public static function getGroupActionParam($iPolicyId, $sModuleClassName, $sParamName)
    {
        $action = GroupPolicyFunc::find()
            ->where([
                'policy_id' => $iPolicyId,
                'module_name' => $sModuleClassName,
                'param_name' => $sParamName,
            ])
            ->asArray()
            ->one();

        if ($action === null) {
            return 0;
        }

        return $action['value'];
    }

    /**
     * Здалить значение параметра функционального уровня для группы.
     *
     * @static
     *
     * @param int $iPolicyId id политики
     * @param string $sModuleClassName имя класса модуля
     * @param string $sParamName имя параметра модуля
     *
     * @return bool
     */
    public static function delGroupActionParam($iPolicyId, $sModuleClassName, $sParamName)
    {
        if ($action = GroupPolicyFunc::findOne(
            [
                'policy_id' => $iPolicyId,
                'module_name' => $sModuleClassName,
                'param_name' => $sParamName,
            ]
        )
        ) {
            $action->value = 0;

            return $action->save();
        }

        return false;
    }

    /**
     * @static Возвращает массив данных групповой политики
     *
     * @param $iPolicyId
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getGroupPolicyData($iPolicyId)
    {
        $aResultArray = [];
        // Выбираем кэш списка разделов, доступных для чтения
        $aCacheArrays = GroupPolicyData::findOne(['policy_id' => $iPolicyId])
            ->getAttributes(['cache_read', 'version', 'start_section', 'read_disable']);

        if (!$aCacheArrays) {
            throw new \Exception('Ошибка при загрузке данных политики доступа');
        }
        $aResultArray['start_section'] = $aCacheArrays['start_section'];

        $aResultArray['version'] = $aCacheArrays['version'];
        // Разбиваем список разделов, доступных для чтения по запятой и формируем из них массив
        $aResultArray['read_access'] = (!empty($aCacheArrays['cache_read'])) ? explode(',', $aCacheArrays['cache_read']) : [];
        // Получаем массив функциональных политик доступа
        $aResultArray['actions_access'] = self::getGroupActionData($iPolicyId);
        // Получаем массив доступных модулей
        $aResultArray['modules_access'] = self::getGroupModuleData($iPolicyId);
        /// Получаем список запрещенных к чтению разделов

        $aResultArray['read_disable'] = self::getDenySections((!empty($aCacheArrays['read_disable'])) ? explode(',', $aCacheArrays['read_disable']) : []);

        return $aResultArray;
    }

    /**
     * Возвращает список разделов, запрещенных для чтения по корневым запрещенным разделам
     *
     * @param $aRootDenySections
     *
     * @return array
     */
    public static function getDenySections($aRootDenySections)
    {
        if (!count($aRootDenySections)) {
            return [];
        }

        $out = [];
        foreach ($aRootDenySections as $iSectionId) {
            $out[] = $iSectionId;

//            $oTree = new Tree();
//            $aDenySections = $oTree->getAllSections($iSectionId);
            $aDenySections = \skewer\base\section\Tree::getSectionTree($iSectionId);
            if (!count($aDenySections)) {
                continue;
            }

            if ($aDenySections) {
                foreach ($aDenySections as $aSection) {
                    $out[] = $aSection['id'];
                }
            }
        }

        return $out;
    }

    /**
     * Возвращает массив разрешенных модулей для групповой политики.
     *
     * @static
     *
     * @param $iPolicyId
     *
     * @return array|bool
     */
    public static function getGroupModuleData($iPolicyId)
    {
        $out = [];

        if ($modules = GroupPolicyModule::find()
            ->select(['module_name'])
            ->where(['policy_id' => $iPolicyId])
            ->asArray()
            ->all()
            ) {
            // Пересобераем массив в нужном виде
            foreach ($modules as $module) {
                $out[$module['module_name']] =
                    [
                        'value' => 1,
                    ];
            }
        }

        return $out;
    }

    /**
     * Добавляет/обновляет модуль в списке модулей для политики.
     *
     * @static
     *
     * @param int $iPolicyId id политики
     * @param string $sModuleClassName имя класса модуля
     * @param $sModuleClassTitle
     *
     * @return bool
     */
    public static function addModule($iPolicyId, $sModuleClassName, $sModuleClassTitle)
    {
        if (!$module = GroupPolicyModule::findOne(['policy_id' => $iPolicyId, 'module_name' => $sModuleClassName])) {
            $module = new GroupPolicyModule();
            $module->policy_id = $iPolicyId;
            $module->module_name = $sModuleClassName;
        }

        $module->title = $sModuleClassTitle;

        return $module->save();
    }

    public static function checkModule($iPolicyId, $sModuleClassName)
    {
        $iGroupModule = GroupPolicyModule::find()
            ->where(['policy_id' => $iPolicyId, 'module_name' => $sModuleClassName])
            ->count();

        return $iGroupModule;
    }

    /**
     * Удаление модуля из списка модулей политики.
     *
     * @static
     *
     * @param int $iPolicyId id политики
     * @param string $sModuleClassName имя класса модуля
     *
     * @return bool
     */
    public static function removeModule($iPolicyId, $sModuleClassName)
    {
        return GroupPolicyModule::deleteAll(['policy_id' => $iPolicyId, 'module_name' => $sModuleClassName]);
    }

    /**
     * Отдает заголовочную запись таблицы групп политик.
     *
     * @static
     *
     * @param int $iPolicyId
     *
     * @return array|bool
     */
    public static function getPolicyHeader($iPolicyId)
    {
        if ($policy = GroupPolicy::findOne(['id' => $iPolicyId])) {
            return $policy->getAttributes();
        }

        return false;
    }

    /**
     * Добавить ветку запрещения/разрешения чтения/записи для пользователя.
     *
     * @static
     *
     * @param int $sectionId id раздела
     * @param int $accessLevel уровень доступа
     * @param int $userId id пользователя
     *
     * @return bool
     */
    public static function setUserAccessFrom(/* @noinspection PhpUnusedParameterInspection */$sectionId, /* @noinspection PhpUnusedParameterInspection */$accessLevel, /* @noinspection PhpUnusedParameterInspection */$userId)
    {
        return false;
    }

    /**
     * Отменить (исключить из политики) ветку доступа для пользователя.
     *
     * @static
     *
     * @param int $sectionId id раздела
     * @param int $userId id пользователя
     *
     * @return bool
     */
    public static function unsetUserAccessFrom(/* @noinspection PhpUnusedParameterInspection */$sectionId, /* @noinspection PhpUnusedParameterInspection */$userId)
    {
        return false;
    }

    /**
     * Обновить версию политики для проверки существования всех закэшированных
     * в сессии пользователей и их разлогивании при необходимости.
     */
    public static function incPolicyVersion()
    {
        $iPolicy = SysVar::get('policy_version_counter');

        if (!$iPolicy) {
            $iPolicy = 0;
        }

        SysVar::set('policy_version_counter', ++$iPolicy);
        /*
         * Устанавливаем флаг в 0, чтобы версия обновилась
         */
        self::$currentVersion = 0;

        return true;
    }

    // func

    /**
     * Возвращает актуальную версию политик доступа.
     *
     * @return null|bool|string
     */
    public static function getPolicyVersion()
    {
        if (!self::$currentVersion) {
            self::$currentVersion = (int) SysVar::get('policy_version_counter');
        }

        return self::$currentVersion;
    }

    // func

    /**
     * Отдает названий политик.
     *
     * @param array $aFilter
     *
     * @return array
     */
    public static function getPolicyTitleList()
    {
        $out = [];

        if ($list = GroupPolicy::find()
            ->select(['id', 'title'])
            ->asArray()
            ->all()) {
            foreach ($list as $policy) {
                $out[$policy['id']] = $policy['title'];
            }
        }

        return $out;
    }

    /**
     * Сохраняет запись политики.
     *
     * @static
     *
     * @param $aData
     *
     * @throws ARSaveException
     *
     * @return bool|int
     */
    public static function update($aData)
    {
        // данный метод сохраняет данные в две таблицы
        // в group_policy по id
        // в group_policy_data по policy_id=id

        $id = (int) $aData['id'];

        if (!$id or !$policy = GroupPolicy::findOne(['id' => $id])) {
            $policy = new GroupPolicy();
        }

        $policy->setAttributes($aData);
        if (!$policy->save()) {
            throw new ARSaveException($policy);
        }

        if (!$policyData = GroupPolicyData::findOne(['policy_id' => $policy->id])) {
            $policyData = new GroupPolicyData();
            $policyData->policy_id = $policy->id;
            $policyData->version = 0;
            $policyData->start_section = 0;
        }

        $policyData->setAttributes($aData);

        if ($policyData->save()) {
            self::incPolicyVersion();

            return $policyData->policy_id;
        }
        throw new ARSaveException($policy);
    }

    /**
     * Удаляет политику.
     *
     * @static
     *
     * @param $iItemId
     *
     * @return bool|int
     */
    public static function delete($iItemId)
    {
        if (!$iItemId = (int) $iItemId) {
            return false;
        }

        GroupPolicy::deleteAll(['id' => $iItemId]);
        GroupPolicyData::deleteAll(['policy_id' => $iItemId]);
        GroupPolicyFunc::deleteAll(['policy_id' => $iItemId]);
        GroupPolicyModule::deleteAll(['policy_id' => $iItemId]);

        self::incPolicyVersion();

        return true;
    }

    /**
     * Возвращает набор доступных текущему пользователю политик для создания пользователей.
     *
     * @static
     *
     * @return array
     */
    public static function getAllowedPolicyList()
    {
        $aOut = [];
        $aPolicyList = GroupPolicy::find()->asArray()->all();

        foreach ($aPolicyList as $aPolicy) {
            /* Пропускаем политику системного администратора */
            if ($aPolicy['alias'] == 'sysadmin') {
                continue;
            }
            if ($aPolicy['alias'] == 'admin' && !CurrentAdmin::isAdminPolicy()) {
                continue;
            }

            // да, именно так, потому что у sys активность -1. lol
            if ($aPolicy['active'] === 0) {
                continue;
            }

            if ($aPolicy['alias'] != 'default') {
                $aOut[$aPolicy['id']] = $aPolicy['title'];
            }
        }

        return $aOut;
    }

    /**
     * Проверяем доступ к политики.
     *
     * @param $iPolicyId
     *
     * @throws \Exception
     */
    public static function testAccessToPolicy($iPolicyId)
    {
        // системному админу можно все
        if (CurrentAdmin::isSystemMode()) {
            return;
        }
        if (!self::hasAccessToPolicy($iPolicyId)) {
            throw new \Exception(\Yii::t('auth', 'no_access_to_record'));
        }
    }

    public static function getPolicyDetail($iPolicyId)
    {
        $aRow = Query::SelectFrom('group_policy')
            ->fields(['id', 'title', 'alias', 'area', 'access_level', 'active', 'start_section', 'read_enable', 'read_disable'])
            ->join('left', 'group_policy_data', 'gpd', 'group_policy.id = gpd.policy_id')
            ->where('group_policy.id', $iPolicyId)
            ->getOne();

        return $aRow;
    }

    /** @var array набор допустимых областей видимостей */
    public static function getAreaList()
    {
        return [
            'public' => 'public',
            'admin' => 'admin',
        ];
    }

    /**
     * Отдает набор допустимых областей видимости.
     *
     * @static
     *
     * @return array
     */
    public static function getAreaListTitles()
    {
        $aAreaList = [];
        foreach (self::getAreaList() as $key => $sArea) {
            $aAreaList[$key] = \Yii::t('auth', $sArea);
        }

        return $aAreaList;
    }

    /**
     * Отдает флаг наличия такой области видимости.
     *
     * @static
     *
     * @param string $sArea
     *
     * @return bool
     */
    public static function hasArea($sArea)
    {
        return isset(self::getAreaList()[$sArea]);
    }

    /**
     * Отдает шаблонный набор значений для добавления новой записи.
     *
     * @return array Политика доступа
     */
    public static function getBlankValues()
    {
        return [
            'title' => \Yii::t('auth', 'new_policy'),
            'publication_date' => date('d.m.Y', time()),
            'active' => 1,
            'access_level' => 2,
        ];
    }

    /**
     * Возвращает true если возможна работа с политикой $iPolicyId текущему администратору.
     *
     * @param $iPolicyId
     *
     * @return bool
     */
    public static function hasAccessToPolicy($iPolicyId)
    {
        $aItemPolicy = Policy::getPolicyDetail($iPolicyId);

        return isset($aItemPolicy['access_level']) && $aItemPolicy['access_level'] >= CurrentAdmin::getAccessLevel();
    }

    /**
     * Сохранение данных функциональной политики.
     *
     * @static
     *
     * @param array $aData - набор данных для сохранения
     * @param mixed $iCurPolicyId
     *
     * @return bool
     */
    public static function saveFuncData($aData, $iCurPolicyId = 0)
    {
        // выйти, если данных недостаточно
        if (!$aData or !isset($aData['id'])) {
            return false;
        }

        // id политики
        $iPolicyId = (int) $aData['id'];
        if (!$iPolicyId) {
            return false;
        }

        // все значения функциональных политик
        $aLayerList = \Yii::$app->register->get(config\Vars::POLICY);

        // перебираем все группы
        foreach ($aLayerList as $sLayerName => $aModuleList) {
            if (!$aModuleList) {
                continue;
            }

            foreach ($aModuleList as $sModuleName => $aModule) {
                $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, $sLayerName);

                if (!$oModuleConfig) {
                    continue;
                }

                if (isset($aModule['items'])) {
                    // перебираем все элементы
                    foreach ($aModule['items'] as $aParam) {
                        /***************************************/

                        // сбор имен переменных
                        $sParamName = $aParam['name'];
                        $sModuleName = $oModuleConfig->getNameWithNamespace();

                        // собираем имя переменной
                        $sValName = sprintf('params'. self::DELIMITER_PARAMS . '%s%s' . self::DELIMITER_PARAMS . '%s', $oModuleConfig->getName(), $oModuleConfig->getLayer(), $sParamName);

                        // если есть данные во входном массиве
                        if (isset($aData[$sValName])) {
                            // пришедшее значение
                            $mValue = $aData[$sValName];

                            /*Проверим, есть ли у текущего пользователя права на это*/
                            $sParam = Policy::getGroupActionParam($iCurPolicyId, $sModuleName, $sParamName);

                            if (($sParam) or (CurrentAdmin::isSystemMode())) {
                                // сохранить значение
                                Policy::setGroupActionParam($iPolicyId, $sModuleName, $sParamName, $mValue);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Сохранение данных по модулям для политики.
     *
     * @static
     *
     * @param array $aData - набор данных для сохранения
     * @param mixed $iCurPolicyId
     *
     * @return bool
     */
    public static function saveModuleData($aData, $iCurPolicyId = 0)
    {
        // выйти, если данных недостаточно
        if (!$aData or !isset($aData['id'])) {
            return false;
        }

        // id политики
        $iPolicyId = (int) $aData['id'];
        if (!$iPolicyId) {
            return false;
        }

        // все значения функциональных политик
        $aModuleList = \Yii::$app->register->getModuleList(Layer::TOOL);

        foreach ($aModuleList as $sModuleName) {
            $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, Layer::TOOL);

            $sValName = sprintf('params_module' . self::DELIMITER_PARAMS_MODULE . '%s', $oModuleConfig->getName());

            if (isset($aData[$sValName])) {
                // пришедшее значение
                $mValue = $aData[$sValName];

                if (($mValue && Policy::checkModule($iCurPolicyId, $sModuleName)) or ($mValue && CurrentAdmin::isSystemMode())) {
                    Policy::addModule($iPolicyId, $sModuleName, $oModuleConfig->getTitle());
                } else {
                    Policy::removeModule($iPolicyId, $sModuleName);
                }
            }
        }

        return true;
    }

    /**
     * @static Обновление кэша доступных для чтения разделов для политики доступа
     *
     * @param int $iPolicyId ID политики доступа для которой обновляется кэш
     * @param [] $aInputData Список разделов
     * @param $iPolicyVersion
     */
    public static function updateCacheByPolicyId($iPolicyId, $aInputData, $iPolicyVersion)
    {
        // Формируем строку разделов из массива
        $sReadCache = implode(',', $aInputData['read_access']);

        $aData = [
            'policy_id' => $iPolicyId,
            'cache_read' => $sReadCache,
            'version' => $iPolicyVersion,
        ];

        $sQuery =
            'UPDATE
                `group_policy_data`
            SET
                `cache_read` = :cache_read,
                `version` = :version
            WHERE
                `policy_id`=:policy_id;';

        Query::SQL($sQuery, $aData);
    }

    /**
     * Получение всех разрешенных политикой разделов
     * ~~~
     * Отдает массив массивов
     *  1. ключ # - int[] - массив int - id разделов, доступных для вывода
     *  2. ключ . - [] - типовой массив с данными текущего раздела
     *  3. ключ int (тиких много) - [][] - массив типовых массивов с резделами,
     *          подчиненными для раздела с id = ключу.
     *
     *  Типовой массив имеет структуру
     *     'id' => 278      // int id раздела
     *     'parent' => 277  // int id родительского раздела
     *     'title' => 'FAQ' // str название раздела
     *     'visible' => 1   // int id режима видимости (см класс Visible)
     *     'show' => true   // bool выводить на сайте или нет (true при visible == Visible::VISIBLE )
     *     'link' => ''     // str ссылка с раздела
     * ~~~
     *
     * @param bool $policy Политика доступа
     * @param int $id Выбранный раздел
     * @param bool $bUseCached Кешировать пользовательские выборки
     * @param array $aShowFilter фильтр по видимости разделов
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getAvailableSections($policy = false, $id = 0, $bUseCached = false, $aShowFilter = [])
    {
        if ($bUseCached && Tree::$AvailableSectionCache && $policy == Tree::policyUser) {
            return Tree::$AvailableSectionCache;
        }

        $out = [];

        $query = TreeSection::find()->orderBy('position');

        if (SysVar::get('tree.checkPolicy') && $policy) {
            switch ($policy) {
                case Tree::policyAdmin:
                    $sections = CurrentAdmin::getReadableSections();
                    break;
                case Tree::policyUser:
                    $sections = CurrentUser::getReadableSections();
                    break;
                default:
                    $sections = ArrayHelper::getValue(Policy::getGroupPolicyData($policy), 'read_access', []);
            }

            $query->where(['id' => $sections]);
        }

        if ($aShowFilter) {
            $query->andWhere(['visible' => $aShowFilter]);
        }

        /** @var TreeSection $section */
        foreach ($query->each() as $section) {
            $aSectionData = [
                'id' => $section->id,
                'parent' => $section->parent,
                'title' => $section->title,
                'visible' => $section->visible,
                'show' => in_array($section['visible'], Visible::$aShowInMenu),
                'link' => $section->link,
            ];

            $out[$section->parent][] = $aSectionData;

            $out['#'][$section->id] = $section->parent;

            if ($id && $section->id == $id) {
                $out['.'] = $aSectionData;
            }
        }

        if ($bUseCached && $policy == Tree::policyUser) {
            Tree::$AvailableSectionCache = $out;
        }

        return $out;
    }
} // class
