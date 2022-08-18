<?php

namespace skewer\build\Tool\Policy;

use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\base\ui;
use skewer\build\Tool;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\auth\models\GroupPolicyFunc;
use skewer\components\auth\models\GroupPolicyModule;
use skewer\components\auth\Policy;
use skewer\components\auth\Users;
use skewer\components\config;
use skewer\components\ext;
use yii\base\UserException;

class Module extends Tool\LeftList\ModulePrototype
{
    // текущий номер страницы ( с 0, а приходит с 1 )
    protected $iPage = 0;

    /**
     * Имя панели.
     *
     * @var string
     */
    protected $sPanelName = '';

    /**
     * Метод, выполняемый перед action меодом
     *
     * @throws UserException
     */
    protected function preExecute()
    {
        // номер страницы
        $this->iPage = $this->getInt('page') - 1;
        if ($this->iPage < 0) {
            $this->iPage = 0;
        }
    }

    /**
     * Первичное состояние.
     */
    protected function actionInit()
    {

        if ( Site::isNewAdmin() ){
            $this->showIframeAction();
            return ;
        }
        // вывод списка
        $this->actionList();
    }

    protected function showIframeAction(){
        $this->render(new \skewer\build\Tool\Policy\view\Iframe([]));
    }

    /**
     * Список пользователей.
     */
    protected function actionList()
    {
        /**
         * Данные.
         */
        $q = GroupPolicy::find()->where(['!=', 'active', Policy::stSys]); // не выводим политику сисадмина в списке
        $aItems = $q->asArray()->all();

        /*Если не Sys, скрываем политику админа*/
        if (!CurrentAdmin::isSystemMode()) {
            $aOutItems = [];
            foreach ($aItems as $item) {
                if ($item['alias'] != 'admin') {
                    $aOutItems[] = $item;
                }
            }
            $aItems = $aOutItems;
        }

        /*
         * Интерфейс
         */

        $this->setPanelName(\Yii::t('auth', 'policyList'));

        $aOutItems = [];

        $iPolicyId = Auth::getPolicyId('admin');

        foreach ($aItems as $item) {
            if ($item['id'] != $iPolicyId) {
                $aOutItems[] = $item;
            }
        }

        $this->render(new Tool\Policy\view\Index([
            'aOutItems' => $aOutItems,
        ]));
    }

    /**
     * Отображение формы.
     */
    protected function actionShow()
    {
        /**
         * Данные.
         */

        // номер записи
        $aData = $this->get('data');

        $iItemId = (is_array($aData) && isset($aData['id']) && $aData['id']) ? (int) $aData['id'] : $this->getInt('id');
        $iPolicyId = Auth::getPolicyId('admin');

        if ($iPolicyId == $iItemId) {
            throw new UserException(\Yii::t('auth', 'policy_no_rights'));
        }
        // установка заголовка панели
        $this->setPanelName($iItemId ? \Yii::t('auth', 'edit') : \Yii::t('auth', 'add'), true);

        // запись
        $aItem = $iItemId ? Policy::getPolicyDetail($iItemId) : Policy::getBlankValues();

        if (isset($aItem['access_level']) && $aItem['access_level'] < 2) {
            $aItem['fulladmin'] = 1;
        }

        /**
         * Скрываем активность для default политики.
         */
        $bHide = false;
        if ($iItemId) {
            $aDefaultUser = Users::getDefaultUserData();
            if (isset($aDefaultUser['group_policy_id']) && $aDefaultUser['group_policy_id'] == $iItemId) {
                $bHide = true;
            }
        }

        /**
         * Интерфейс
         */

        // поле - набор галочек функциональных политик
        $params = self::getFuncDataForField($iItemId);
        $params['extendLibName'] = 'CheckSet';

        // поле - набор галочек политики модулей
        $paramsModule = self::getModuleDataForField($iItemId);
        $paramsModule['extendLibName'] = 'CheckSet4Module';

        /*Если не под sys, выведем только те настройки которые доступны текущему пользователю*/
        if (!CurrentAdmin::isSystemMode()) {
            $aParamsModules = [];

            foreach ($paramsModule['items'] as $item) {
                if (Policy::checkModule($iPolicyId, $item['name'])) {
                    $aParamsModules[] = $item;
                }
            }

            $paramsModule['items'] = $aParamsModules;
        }

        array_unshift($paramsModule['items'], [
            'name' => 'set_all',
            'title' => \Yii::t('auth', 'set_all_title'),
            'execute' => 'set_all',
            'value' => '0',
        ]);

        $this->addModuleNoticeReport(\Yii::t('auth', 'show_policy'), $aItem);

        $this->render(new Tool\Policy\view\Show([
            'bHide' => $bHide,
            'sAlias' => $aItem['alias'] ?? '',
            'sAreaListTitles' => Policy::getAreaListTitles(),
            'sArea' => $aItem['area'] ?? '',
            'aParams' => $params,
            'aParamsModule' => $paramsModule,
            'iItemId' => $iItemId,
            'aItem' => $aItem,
            'bIsUsualPolicy' => ($iItemId && !$bHide),
        ]));
    }

    /**
     * Сохранение.
     *
     * @throws \Exception
     */
    protected function actionSave()
    {
        // запросить данные
        $aData = $this->get('data');
        $bHasId = isset($aData['id']) && is_numeric($aData['id']) && $aData['id'];
        $iId = 0;

        // есть данные - сохранить
        if ($aData) {
            // проверка на превышение прав доступа
            if (CurrentAdmin::isLimitedRights() || (isset($aData['access_level']) && $aData['access_level'] && $aData['access_level'] < CurrentAdmin::getAccessLevel())) {
                throw new UserException(\Yii::t('auth', 'policy_no_rights'));
            }
            // уточнение уровня прав доступа
            if (!$aData['access_level']) {
                // если права доступа изначально максимальны, то в базе не меняем флаг - чтоб не испортить сисадмина
                unset($aData['access_level']);
            } elseif (isset($aData['fulladmin']) and $aData['fulladmin'] == 1) {
                $aData['access_level'] = 1;
            } else {
                $aData['access_level'] = 2;
            }

            if (!isset($aData['area'])) {
                throw new UserException(\Yii::t('auth', 'no_area'));
            }
            if (!Policy::hasArea($aData['area'])) {
                throw new UserException(\Yii::t('auth', 'wrong_area'));
            }
            // основная запись
            $iId = Policy::update($aData);

            // набор функциональных параметров
            $aData['id'] = $iId;

            $aUserData = Auth::getUserData('admin');

            Policy::saveFuncData($aData, $aUserData['group_policy_id']);
            Policy::saveModuleData($aData, $aUserData['group_policy_id']);
            Policy::incPolicyVersion();
        }

        if ($iId) {
            if (!$bHasId) {
                /*Если эта политика только что создана*/
                /*Сохраним видимость для некоторых разделов*/
                $aSaveData = [
                    'id' => $iId,
                    'read_enable' => implode(',', [
                        \Yii::$app->sections->getValue('topMenu'),
                        \Yii::$app->sections->getValue('leftMenu'),
                    ]),
                    'read_disable' => '',
                    'start_section' => 0,
                ];

                // сохранение
                Policy::update($aSaveData);
            }

            // если задан id и даные сохранены
            if ($bHasId) {
                // вывод списка
                $this->addMessage(\Yii::t('auth', 'policy_changed'));
                $this->addModuleNoticeReport(\Yii::t('auth', 'editing_policy'), $aData);
                $this->actionList();
            } else {
                // вывод записи на редактиорование
                $this->addModuleNoticeReport(\Yii::t('auth', 'policy_adding'), $aData);
                $this->set('id', $iId);
                $this->actionShow();
            }
        }
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        try {
            // запросить данные
            $aData = $this->get('data');

            // id записи
            $iItemId = (is_array($aData) and isset($aData['id'])) ? (int) $aData['id'] : 0;

            // проверка на возможность удаления политики
            $aGroupPolicy = Policy::getPolicyHeader($iItemId);
            if (!$aGroupPolicy || !isset($aGroupPolicy['del_block']) || $aGroupPolicy['del_block']) {
                throw new \Exception(\Yii::t('auth', 'delete_no_rights'));
            }
            // удалять можно только дочерние политики
            if (CurrentAdmin::isLimitedRights() || (isset($aData['access_level']) && $aData['access_level'] && $aData['access_level'] < CurrentAdmin::getAccessLevel())) {
                throw new \Exception(\Yii::t('auth', 'access_no_rights'));
            }
            // удаление
            if (!($iRes = Policy::delete($iItemId))) {
                throw new \Exception(\Yii::t('auth', 'delete_error'));
            }
            $this->addMessage(\Yii::t('auth', 'policy_deleted'));
            $this->addModuleNoticeReport(\Yii::t('auth', 'policy_deleting'), $aData);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        // вывод списка
        $this->actionList();
    }

    /**
     * Сохранение политики доступа по разделам
     *
     * @throws UserException
     */
    protected function actionSaveSections()
    {
        // проверка на превышение прав доступа
        if (CurrentAdmin::isLimitedRights() || (isset($aData['access_level']) && $aData['access_level'] && $aData['access_level'] < CurrentAdmin::getAccessLevel())) {
            throw new \Exception(\Yii::t('auth', 'access_no_rights'));
        }
        // запросить идентификатор сущности
        $iItemId = $this->getInt('id');

        // запросить данные для сохранения
        $iStartSection = $this->getInt('startSection');
        $aItemsA = $this->get('itemsAllow');
        $aItemsD = $this->get('itemsDeny');

        // проверка пришедщих данных
        if (!$iItemId) {
            throw new UserException(\Yii::t('auth', 'policy_id_expected'));
        }
        if (!is_array($aItemsA) or !is_array($aItemsD)) {
            throw new UserException(\Yii::t('auth', 'wrong_data_format'));
        }
        $aUserData = Auth::getUserData('admin');
        $aCurData = Policy::getPolicyDetail($aUserData['group_policy_id']);

        $aSectionIdsDisabled = [];

        if (isset($aCurData['read_disable']) && $aCurData['read_disable'] !== '') {
            $aTmp = explode(',', $aCurData['read_disable']);

            foreach ($aTmp as $item) {
                /*В массив разрешенных добавим корневой раздел*/
                $aSectionIdsDisabled[$item] = (int) $item;
                /*и всех потомков*/
                $aSectionIds = Tree::getAllSubsection($item);
                foreach ($aSectionIds as $key => $section) {
                    $aSectionIdsDisabled[$key] = $section;
                }
            }
        }

        // запрос данных политики
        $aData = Policy::getPolicyDetail($iItemId);
        if (!$aData) {
            throw new UserException(\Yii::t('auth', 'policy_not_found'));
        }
        $aItemsD = self::setDisabledRec($aItemsA, $aSectionIdsDisabled, $aItemsD);

        // сформировать массив для сохраненния
        $aSaveData = [
            'id' => $iItemId,
            'read_enable' => implode(',', $aItemsA),
            'read_disable' => implode(',', $aItemsD),
            'start_section' => $iStartSection,
        ];

        if (isset($aSaveData['read_disable']) && $aSaveData['read_disable'] !== '') {
            $aTmp = explode(',', $aSaveData['read_disable']);

            $aRootSections = \Yii::$app->sections->getValues('404');

            foreach ($aTmp as $item) {
                $aSectionIds = Tree::getAllSubsection($item);
                $aSectionIds[$item] = $item;

                foreach ($aSectionIds as $item2) {
                    if (array_search($item, $aRootSections) !== false) {
                        $this->actionSections();
                        throw new UserException(\Yii::t('auth', 'section_unclosed') . $item2);
                    }
                }
            }
        }
        // сохранение
        $iRes = Policy::update($aSaveData);

        if ($iRes) {
            $this->addMessage(\Yii::t('auth', 'policy_updated'));
            $this->addModuleNoticeReport(\Yii::t('auth', 'sections_change'), $aData);
        } else {
            $this->addMessage(\Yii::t('auth', 'changing_sections_error'));
        }

        $this->actionShow();
    }

    /**
     * Форма редактора доступа к разделам
     *
     * @throws UserException
     */
    protected function actionSections()
    {
        // проверка на превышение прав доступа
        if (CurrentAdmin::isLimitedRights() || (isset($aData['access_level']) && $aData['access_level'] && $aData['access_level'] < CurrentAdmin::getAccessLevel())) {
            throw new \Exception(\Yii::t('auth', 'access_no_rights'));
        }
        // идентификатор политики
        $iItemId = $this->get('id');
        if (!$iItemId) {
            throw new UserException(\Yii::t('auth', 'policy_id_expected'));
        }
        // запрос данных политики
        $aData = Policy::getPolicyDetail($iItemId);
        if (!$aData) {
            throw new UserException(\Yii::t('auth', 'policy_not_found'));
        }
        // объект для построения списка
        $oIface = new ext\UserFileView('ReadSections');
        $oIface->setModuleLangValues(['polisyAllow', 'polisyNone', 'polisyDeny', 'polisyMain']);

        // заголовок панели
        $this->setPanelName(\Yii::t('auth', 'policySection') . ' "' . $aData['title'] . '"');

        // сборка дерева разделов
        $this->setData('items', Tree::getSectionTree(0));

        /* Добавляем css файл для */
        $this->addCssFile('policy_tree.css');

        // разрешенные и запрещенные разделы
        $this->setData('startSection', (int) $aData['start_section']);
        $this->setData('itemsAllow', explode(',', $aData['read_enable']));
        $this->setData('itemsDeny', explode(',', $aData['read_disable']));

        // кнопки
        $oIface->addBtnSave('', 'saveSection', ['id' => $iItemId]);
        $oIface->addBtnCancel('show', '', ['id' => $iItemId]);

        // вывод данных в интерфейс
        $this->setInterface($oIface);
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
            'page' => $this->iPage,
            'url' => '/oldadmin/?mode=policy'
        ]);
    }

    /**
     * Возвращает массив для инициализации спец компонента в js, отвечающего
     *      за набор параметров по модулям в рамках политики.
     *
     * @param $iPolicyId
     *
     * @return array
     */
    public static function getModuleDataForField($iPolicyId)
    {
        // все значения функциональных политик
        $aModuleList = \Yii::$app->register->getModuleList(Layer::TOOL);

        // запросить данные для данной политики
        $aSetData = Policy::getGroupModuleData($iPolicyId);

        // выходной массив
        $aOut = [
            'name' => 'params_module',
            'title' => \Yii::t('auth', 'access_options'),
            'value' => [],
        ];

        $aItems = [];
        foreach ($aModuleList as $sModuleName) {
            $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, Layer::TOOL);

            // модули помеченные как "системные" пропускаем
            if ($oModuleConfig->getVal('isSystem')) {
                continue;
            }

            // сбор имен переменных
            if (isset($aSetData[$sModuleName])) {
                $sVal = '1';
            } else {
                $sVal = '';
            }

            // добавляем параметр в список
            $aItems[] = [
                'name' => $oModuleConfig->getName(),
                'title' => $oModuleConfig->getTitle(),
                'value' => $sVal,
            ];
        }

        $aOut['items'] = $aItems;

        return $aOut;
    }

    /**
     * Возвращает массив для инициализации спец компонента в js, отвечающего
     *      за набор параметров по подулям в рамках политики.
     *
     * @param $iPolicyId
     *
     * @return array
     */
    public static function getFuncDataForField($iPolicyId)
    {
        // все значения функциональных политик
        $aAllData = \Yii::$app->register->get(config\Vars::POLICY);

        // запросить данные для данной политики
        $aSetData = Policy::getGroupActionData($iPolicyId);

        // выходной массив
        $aOut = [
            'name' => 'params',
            'title' => \Yii::t('auth', 'function_options'),
            'value' => [
                'groups' => [],
            ],
        ];

        // ссылка на ветку для удобства
        $aGroupsRef = &$aOut['value']['groups'];

        // перебираем все группы
        foreach ($aAllData as $sLayerName => $aModuleList) {
            if (!$aModuleList) {
                continue;
            }

            foreach ($aModuleList as $sModuleName => $aModule) {
                $oModuleConfig = \Yii::$app->register->getModuleConfig($sModuleName, $sLayerName);

                if (!$oModuleConfig) {
                    continue;
                }

                // новая группа
                $aNewGroup = [
                    'name' => $sModuleName . $sLayerName,
                    'title' => $oModuleConfig->getTitle(),
                    'items' => [],
                ];

                if (isset($aModule['items'])) {
                    // перебираем все элементы
                    foreach ($aModule['items'] as $aParam) {
                        $aParam['title'] = \Yii::t($oModuleConfig->getLanguageCategory(), $aParam['name']);

                        // сбор имен переменных
                        $sParamName = $aParam['name'];
                        $sModuleName = $oModuleConfig->getNameWithNamespace();

                        // запрос значения
                        if (isset($aSetData[$sModuleName]) and
                            isset($aSetData[$sModuleName][$sParamName]) and
                            isset($aSetData[$sModuleName][$sParamName]['value'])
                        ) {
                            $aParam['value'] = $aSetData[$sModuleName][$sParamName]['value'];
                        } else {
                            $aParam['value'] = '';
                        }

                        $iCurPolicyId = Auth::getPolicyId('admin');

                        // добавляем параметр в список
                        $sParam = Policy::getGroupActionParam($iCurPolicyId, $sModuleName, $sParamName);

                        if (($sParam) or CurrentAdmin::isSystemMode()) {
                            $aNewGroup['items'][] = $aParam;
                        }
                    }
                }

                // добавляем группу
                $aGroupsRef[] = $aNewGroup;
            }
        }

        return $aOut;
    }

    private static function setDisabledRec($aSections, $aLockSections, $aItemsD)
    {
        foreach ($aSections as $item) {
            $aItemsD = self::setDisabled($item, $aItemsD, $aLockSections);
        }

        $aItemsD = array_unique($aItemsD);

        return $aItemsD;
    }

    private static function setDisabled($iSectionId, $aItemsD, $aLockSections)
    {
        if (array_search($iSectionId, $aLockSections) !== false) {
            $aItemsD[] = $iSectionId;
        } else {
            $aChilds = Tree::getSubSections($iSectionId, true);

            foreach ($aChilds as $item) {
                $aItemsD = self::setDisabled($item['id'], $aItemsD, $aLockSections);
            }
        }

        return $aItemsD;
    }

    /**
     * Копирование политики доступа.
     */
    protected function actionClone()
    {
        $aData = $this->get('data');
        // id копируемой политики
        $iCopyPolicyId = (is_array($aData) && isset($aData['id']) && $aData['id']) ? (int) $aData['id'] : $this->getInt('id');
        $aItem = Policy::getPolicyDetail($iCopyPolicyId);

        $aItem['id'] = '0';
        $aItem['title'] .= ' (копия)';

        // $iId новой политики
        $iId = Policy::update($aItem);

        $groupPolicyFunc = GroupPolicyFunc::find()
            ->where(['policy_id' => $iCopyPolicyId])
            ->asArray()
            ->all();

        foreach ($groupPolicyFunc as $item) {
            $newGroupPolicyFunc = new GroupPolicyFunc();
            $newGroupPolicyFunc->policy_id = $iId;
            $newGroupPolicyFunc->module_name = $item['module_name'];
            $newGroupPolicyFunc->param_name = $item['param_name'];
            $newGroupPolicyFunc->value = $item['value'];
            $newGroupPolicyFunc->save();
        }

        $groupPolicyModule = GroupPolicyModule::find()
            ->where(['policy_id' => $iCopyPolicyId])
            ->asArray()
            ->all();

        foreach ($groupPolicyModule as $item) {
            $newGroupPolicyModule = new GroupPolicyModule();
            $newGroupPolicyModule->policy_id = $iId;
            $newGroupPolicyModule->module_name = $item['module_name'];
            $newGroupPolicyModule->title = $item['title'];
            $newGroupPolicyModule->save();
        }

        Policy::checkCache($iId);
        $this->actionList();
    }
}
