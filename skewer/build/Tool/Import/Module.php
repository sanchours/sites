<?php

namespace skewer\build\Tool\Import;

use skewer\base\queue\ar\Schedule;
use skewer\base\queue\Manager;
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Cms\FileBrowser;
use skewer\build\Tool;
use skewer\build\Tool\Schedule\Api as ScheduleApi;
use skewer\components\auth\CurrentAdmin;
use skewer\components\i18n\ModulesParams;
use skewer\components\import\Api;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\ar\Log;
use skewer\components\import\Config;
use skewer\components\import\Task;
use yii\base\Exception;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Модуль настройки шаблонов для импорта данных в каталога, запуска импорта и чтения статистики
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * id текущего шаблона.
     *
     * @return int
     */
    private function getTplId()
    {
        $iTpl = $this->getInDataValInt('id');
        if (!$iTpl) {
            $iTpl = $this->getInnerDataInt('tpl_id');
        }

        $this->setInnerData('tpl_id', $iTpl);

        return $iTpl;
    }

    /**
     * Список шаблонов импорта.
     */
    protected function actionList()
    {
        $this->setPanelName(\Yii::t('import', 'tpl_list'));

        $aList = Api::getTemplateList();

        $this->render(new Tool\Import\view\Index([
            'isSys' => CurrentAdmin::isSystemMode(),
            'aList' => $aList,
            'bIsNotDirImport' => (!is_dir(ROOTPATH . 'import/gallery') || !is_dir(ROOTPATH . 'import/file')),
        ]));
    }

    /**
     * Добавление нового шаблона.
     */
    protected function actionAdd()
    {
        $this->showHeadSettingsForm();
    }

    /**
     * Основные настройки.
     *
     * @param null|int $iTpl
     */
    protected function actionHeadSettings($iTpl = null)
    {
        if (!$iTpl) {
            $iTpl = $this->getTplId();
        }

        if (CurrentAdmin::isSystemMode()) {
            $this->showHeadSettingsForm($iTpl);
        } else {
            $this->showClientForm($iTpl);
        }
    }

    /**
     * Форма основных настроек.
     *
     * @param null|int $id id шаблона
     */
    private function showHeadSettingsForm($id = null)
    {
        $this->setPanelName(\Yii::t('import', 'head_settings_form'));

        $oTemplate = Api::getTemplate($id);

        $aData = $oTemplate->getData();

        if (isset($aData['type']) && $aData['type'] == 0) {
            $aData['type'] = Api::Type_File;
        }

        if ($oTemplate->type == Api::Type_File) {
            $aData['source_file'] = $aData['source'];
        } else {
            $aData['source_str'] = $aData['source'];
        }

        $this->render(new Tool\Import\view\HeadSettingsForm([
            'sGroup' => \Yii::t('import', 'head_settings_form'),
            'aCardList' => Api::getCardList(),
            'aProviderTypeList' => Api::getProviderTypeList(),
            'aTypeList' => Api::getTypeList(),
            'aCodingList' => Api::getCodingList(),
            'aData' => $aData,
            'id' => $id,
            'isNewAdmin' => Site::isNewAdmin()
        ]));
    }

    /**
     * Форма основных настроек для клиента.
     *
     * @param null|int $id id шаблона
     */
    private function showClientForm($id = null)
    {
        $this->setPanelName(\Yii::t('import', 'head_settings_form'));

        $oTemplate = Api::getTemplate($id);

        $aData = $oTemplate->getData();

        if ($oTemplate->type == Api::Type_File) {
            $aData['source_file'] = $aData['source'];
        } else {
            $aData['source_str'] = $aData['source'];
        }

        $sGroup = \Yii::t('import', 'head_settings_form');

        $this->render(new Tool\Import\view\ClientForm([
            'sGroup' => $sGroup,
            'bEqualTypes' => ($oTemplate->type == Api::Type_File),
            'aData' => $aData,
        ]));
    }

    /**
     * Настройки провайдера.
     *
     * @throws UserException
     */
    protected function actionProviderSettings()
    {
        $iTpl = $this->getTplId();
        $this->showProviderSettingsForm($iTpl);
    }

    /**
     * @param mixed $iTpl
     *
     * @throws UserException
     */
    private function showProviderSettingsForm($iTpl)
    {
        if (!$iTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oTemplate = Api::getTemplate($iTpl);

        if (!$oTemplate) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $this->setPanelName(\Yii::t('import', 'provider_settings_form'));

        $this->render(new Tool\Import\view\ProviderSettingsForm([
            'oTemplate' => $oTemplate,
        ]));
    }

    /**
     * Настройки соответствия полей.
     *
     * @throws \Exception
     */
    protected function actionFields()
    {
        $iTpl = $this->getTplId();
        $this->showFieldsForm($iTpl);
    }

    /**
     * Форма настроек соответствия полей.
     *
     * @param $iTpl
     *
     * @throws UserException
     * @throws \Exception
     */
    private function showFieldsForm($iTpl)
    {
        if (!$iTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oTemplate = Api::getTemplate($iTpl);

        if (!$oTemplate) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $this->setPanelName(\Yii::t('import', 'fields_form'));

        // #39953 Поиск отсутствующих полей импорта в карточке и выдача сообщения
        if ($sMessages = Api::checkImportFields($iTpl)) {
            $this->addMessage(\Yii::t('Import', 'warning'), $sMessages, 5000);
        }

        $this->render(new Tool\Import\view\FieldsForm([
            'oTemplate' => $oTemplate,
        ]));
    }

    /**
     * Форма настройки полей.
     *
     * @throws UserException
     */
    protected function actionFieldsSettings()
    {
        $iTpl = $this->getTplId();
        $this->showFieldSettingsForm($iTpl);
    }

    /**
     * @param null $iTpl
     *
     * @throws UserException
     */
    private function showFieldSettingsForm($iTpl = null)
    {
        if (!$iTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oTemplate = Api::getTemplate($iTpl);

        if (!$oTemplate) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $this->setPanelName(\Yii::t('import', 'fields_settings_form'));

        $this->render(new Tool\Import\view\FieldSettingsForm([
            'oTemplate' => $oTemplate,
        ]));
    }

    /**
     * Сохранение.
     *
     * @throws UserException
     */
    protected function actionSave()
    {
        $aData = $this->getInData();

        $oTpl = Api::getTemplate((isset($aData['id'])) ? $aData['id'] : null);

        if (CurrentAdmin::isSystemMode()) {
            $aRequiredList = ImportTemplate::getModel()->getColumnSet('required');
            foreach ($aRequiredList as $sFieldName) {
                if (!isset($aData[$sFieldName]) || !$aData[$sFieldName]) {
                    throw new UserException(\Yii::t('import', 'not_defined_field', \Yii::t('import', 'field_' . $sFieldName)));
                }
            }
        }

        $sOldType = $oTpl->provider_type;
        $sOldCard = $oTpl->card;
        $oTpl->setData($aData);

        if ($oTpl->type == Api::Type_File) {
            $oTpl->source = (isset($aData['source_file'])) ? $aData['source_file'] : '';
        } else {
            $oTpl->source = (isset($aData['source_str'])) ? $aData['source_str'] : '';
        }

        if (!$oTpl->source) {
            throw new UserException(\Yii::t('import', 'not_defined_field', \Yii::t('import', 'field_source')));
        }

        $id = $oTpl->save();
        if ($id) {
            /* Сменился провайдер или карточка - почистим конфиг */
            if ($sOldType != $oTpl->provider_type || $sOldCard != $oTpl->card) {
                $oConfig = new Config($oTpl);
                $oConfig->clearFields();
                $oTpl->settings = json_encode($oConfig->getData());
                $oTpl->save();
            }
            $this->actionHeadSettings($id);
        } else {
            throw new UserException(\Yii::t('import', 'error_no_save'));
        }
    }

    /**
     * Сохранение настроек провайдера.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionSaveProviderSettings()
    {
        $aData = $this->getInData();

        if (!isset($aData['id'])) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oTpl = Api::getTemplate($aData['id']);

        if (!$oTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oConfig = new Config($oTpl);
        $oProvider = Api::getProvider($oConfig);

        $aVars = $oConfig->getData();

        /* Параметры провайдера */
        foreach ($oProvider->getParameters() as $key => $value) {
            $aVars[$key] = (isset($aData[$key])) ? $aData[$key] : ((isset($value['default'])) ? $value['default'] : '');
        }

        $oTpl->settings = json_encode($aVars);
        $oTpl->save();
    }

    /**
     * Сохранение настроек соответсвия полей.
     *
     * @throws UserException
     */
    protected function actionSaveFields()
    {
        $aData = $this->getInData();

        if (!isset($aData['id'])) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oTpl = Api::getTemplate($aData['id']);

        if (!$oTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oConfig = new Config($oTpl);
        $oConfig->setFields($aData, true);

        $oTpl->settings = $oConfig->getJsonData();

        $oTpl->save();

        /** Проверка на наличие уникального поля */
        $bUnique = false;
        foreach ($aData as $sKey => $value) {
            if (preg_match('/type_(\w+)/', $sKey)) {
                if ($value === 'Unique') {
                    $bUnique = true;
                    break;
                }
            }
        }

        if (!$bUnique) {
            throw new UserException(\Yii::t('import', 'error_unique_field_not_found'));
        }
    }

    /**
     * Сохранение настроек полей.
     *
     * @throws UserException
     * @throws Exception
     */
    protected function actionSaveSettingsFields()
    {
        $aData = $this->getInData();

        if (!isset($aData['id'])) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oTpl = Api::getTemplate($aData['id']);

        if (!$oTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $oConfig = new Config($oTpl);

        /** Валидация полей */
        $aFieldsConfig = $oConfig->getParam('fields');

        if (is_array($aFieldsConfig)) {
            foreach ($aFieldsConfig as $aItemFieldConfig) {
                if (empty($aItemFieldConfig['type'])) {
                    continue;
                }

                /** @var \skewer\components\import\field\Prototype $sClassName */
                $sClassName = 'skewer\\components\\import\\field\\' . $aItemFieldConfig['type'];
                if (!class_exists($sClassName)) {
                    continue;
                }

                $aParams = $sClassName::getParameters();

                foreach ($aParams as $k => $value) {
                    $sFormKeyName = 'params_' . mb_strtolower($aItemFieldConfig['type']) . ':' . $k;

                    if (!isset($aData[$sFormKeyName]) || !isset($value['validator'])) {
                        continue;
                    }

                    $aValidatorConfig = $value['validator'];

                    if (is_array($aValidatorConfig) && isset($aValidatorConfig[0], $aValidatorConfig[1])) { //  0 - тип валидатора, 1 - атрибуты
                        $sError = '';
                        $oValidator = self::buildValidator($aValidatorConfig[0], $aValidatorConfig[1]);
                        $bRes = $oValidator->validate($aData[$sFormKeyName], $sError);

                        if (!$bRes) {
                            throw new UserException(\Yii::t('import', 'field_section_' . $k) . ': ' . $sError);
                        }
                    }
                }
            }
        }

        $oConfig->setFieldsParam($aData);

        $oTpl->settings = $oConfig->getJsonData();
        $oTpl->save();
    }

    /**
     * Удаление шаблона.
     */
    protected function actionDelete()
    {
        $id = $this->getInDataValInt('id');

        if ($id) {
            ImportTemplate::delete($id);
            Api::deleteLog4Template($id);
        }

        $this->actionList();
    }

    /**
     * Задача.
     */
    protected function actionShowTask()
    {
        $this->setPanelName(\Yii::t('import', 'task_form'));

        $command = json_encode([
            'class' => '\skewer\components\import\Task',
            'parameters' => ['tpl' => $this->getTplId()],
        ]);

        $aData = [];
        /** @var Schedule $task */
        $task = Schedule::findOne(['command' => $command]);

        if ($task) {
            foreach ($task as $key => $val) {
                $aData[$key] = $val;
            }

            $aData['schedule_id'] = $task->id;
        } else {
            $aData = ScheduleApi::getBlankSettingTime();
        }

        $this->render(new Tool\Import\view\ShowTask([
            'aData' => $aData,
        ]));
    }

    /**
     * Сохранение задачи.
     *
     * @throws \Exception
     * @throws ui\ARSaveException
     */
    protected function actionSaveTask()
    {
        $aData = $this->getInData();

        $command = json_encode([
            'class' => '\skewer\components\import\Task',
            'parameters' => ['tpl' => (int) $this->getTplId()],
        ]);

        /** @var \skewer\components\import\ar\ImportTemplateRow $oTpl */
        $oTpl = ImportTemplate::find($this->getTplId());
        if (!$oTpl) {
            throw new \Exception(\Yii::t('import', 'error_tpl_not_fount'));
        }
        //save
        $aData['id'] = $aData['schedule_id'];
        $aData['command'] = $command;
        $aData['name'] = 'import_' . $oTpl->id;
        $aData['title'] = \Yii::t('import', 'task_title', $oTpl->title);
        $aData['priority'] = Task::priorityHigh;
        $aData['resource_use'] = Task::weightHigh;
        $aData['target_area'] = 3;

        if (!$scheduleItem = Schedule::findOne($aData['id'])) {
            $scheduleItem = new Schedule();
            unset($aData['id']);
        }

        $scheduleItem->setAttributes($aData);

        if (!$scheduleItem->save()) {
            throw new ui\ARSaveException($scheduleItem);
        }
        $this->actionHeadSettings();
    }

    /**
     * Запуск импорта.
     *
     * @throws UserException
     * @throws \Exception
     */
    protected function actionRunImport()
    {
        $aData = $this->getInData();
        if (empty($aData) && $this->get('params')) {
            $aData = $this->get('params');
            $aData = $aData[0];
        }

        $taskId = (isset($aData['taskId'])) ? $aData['taskId'] : 0;

        $iTpl = (isset($aData['id'])) ? $aData['id'] : 0;
        if (!$iTpl) {
            $iTpl = $this->getInnerDataInt('tpl_id');
        }

        if (!$iTpl && !$taskId) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        if ($iTpl) {
            $this->setInnerData('tpl_id', $iTpl);
            $this->setInnerData('importRun', $iTpl);
        }

        /** @var ImportTemplateRow $oTpl */
        if (!$oTpl = ImportTemplate::find($iTpl)) {
            throw new \Exception(\Yii::t('import', 'error_tpl_not_fount'));
        }
        /** Запуск импорта */
        $aRes = Task::runTask(Task::getConfigTask($oTpl), $taskId);

        if (in_array($aRes['status'], [Task::stFrozen, Task::stWait])) {
            /* Крутим, ели еще нужно */
            $this->addJSListener('runImport', 'runImport');
            $this->fireJSEvent('runImport', ['taskId' => $aRes['id']]);
        } elseif ($oTemplate = Api::getTemplate($iTpl)) {
            // #39953 Поиск отсутствующих полей импорта в карточке и выдача сообщения в конце импорта
            if ($sMessages = Api::checkImportFields($iTpl)) {
                $this->addMessage(\Yii::t('Import', 'warning'), $sMessages, 5000);
            }
        }

        $this->setInnerData('taskId', $aRes['id']);
        $this->showLog($aRes['id'], CurrentAdmin::isSystemMode());
    }

    /**
     * Список логов.
     *
     * @throws UserException
     */
    protected function actionLogList()
    {
        $this->setPanelName(\Yii::t('import', 'logs_list'));

        $iTpl = $this->getInnerDataInt('tpl_id');
        if (!$iTpl) {
            throw new UserException(\Yii::t('import', 'error_tpl_not_fount'));
        }

        $this->setInnerData('importRun', false);

        $this->render(new view\LogList([
            'sWidgetClsName' => (__NAMESPACE__ . '\View'),
            'aLogs' => Api::getLogs($iTpl),
        ]));
    }

    /**
     * Детальная лога.
     *
     * @throws \Exception
     */
    protected function actionDetailLog()
    {
        $iTpl = $this->getInDataValInt('id_log');
        $this->setInnerData('taskId', $iTpl);
        $this->showLog($iTpl, true, 0, 10000);
    }

    /**
     * Показать лог импорта.
     *
     * @param int $id - id задачи
     * @param bool $bIsDetail - подробный лог c пагинатором?
     * @param int $iPageNum - номер страницы пагинатора
     * @param int $iOnPage - количество, выводимых записей на страницу
     *
     * @throws UserException
     */
    private function showLog($id, $bIsDetail = false, $iPageNum = 0, $iOnPage = 2000)
    {
        if (!$id) {
            throw new UserException(\Yii::t('import', 'error_task_not_fount'));
        }

        // Краткая информация о результатах импорта
        $aParams = Log::getNonListParams($id);

        $aPaginatorData = [];
        $aLogParams = [];
        // Подробная информация с пагинатором
        if ($bIsDetail) {
            $iCount = 0;
            $aListParams = Log::getListParams($id, $iOnPage, $iPageNum, $iCount);

            $aParams = array_merge($aParams, $aListParams);
            $aPaginatorPages = range(1, ceil($iCount / $iOnPage), 1);
            $aPaginatorData = [
                'bShowPaginator' => true,
                'iPaginatorPage' => $iPageNum,
                'aPaginatorPages' => $aPaginatorPages,
            ];
        }

        foreach ($aParams as $aParam) {
            if ($aParam['list']) {
                $aLogParams[$aParam['name']][] = $aParam['value'];
            } else {
                $aLogParams[$aParam['name']] = $aParam['value'];
            }
        }

        if (isset($aLogParams['status'])) {
            $aLogParams['status'] = View::getStatus($aLogParams);
        }

        $baskAction = $this->getInnerData('importRun') ? 'headSettings' : 'logList';

        $sText = $this->renderTemplate('log_template.twig', ['log' => $aLogParams]);

        $this->render(new Tool\Import\view\Log([
            'baskAction' => $baskAction,
            'sText' => $sText,
        ] + $aPaginatorData));
    }

    /**
     * Удаление записи лога.
     *
     * @throws UserException
     */
    protected function actionDeleteLog()
    {
        $id = $this->getInDataValInt('id_log');
        if (!$id) {
            throw new UserException(\Yii::t('import', 'error_task_not_fount'));
        }

        Api::deleteLog($id);

        $this->actionLogList();
    }

    /**
     * Добавление папки импорта.
     */
    protected function actionAddFolder()
    {
        $bCreate = $this->createFolderInRootPath('import');
        if ($bCreate) {
            $this->createFolderInRootPath('import/gallery');
            $this->createFolderInRootPath('import/file');
        }

        $this->actionInit();
    }

    protected function actionSettingTrade()
    {
        $this->render(new Tool\Import\view\SettingsTrade());
    }

    protected function actionSaveSettingsTrade()
    {
        $aFormData = $this->get('data');

        foreach ($aFormData as $sKey => $mParam) {
            SysVar::set("1c.{$sKey}", $mParam);
        }

        $this->actionList();
    }

    /**
     * Построит объект валидатора.
     *
     * @param string $sType тип валидатора
     * @param array $aParams параметры
     *
     * @throws Exception
     * @throws UserException
     *
     * @return object|Validator
     */
    private static function buildValidator($sType, $aParams)
    {
        if (!isset(Validator::$builtInValidators[$sType])) {
            throw new Exception('Не поддерживаемый тип валидатора');
        }
        $aParams['class'] = Validator::$builtInValidators[$sType];

        return \Yii::createObject($aParams);
    }

    /** Очистить очередь задач */
    public function actionClearQueue()
    {
        Manager::clear();
        $this->addMessage(\Yii::t('import', 'queue_cleared'));
    }

    /** Показать страницу лога
     *  @throws UserException
     */
    public function actionGetPageLog()
    {
        $iTaskId = $this->getInnerData('taskId');
        $iPageNum = $this->get('page');

        $this->showLog($iTaskId, true, $iPageNum, 10000);
    }

    /**
     * Обновляет поля источника импорта
     */
    public function actionUpdFieldsSource()
    {
        $aFormData = $this->get('formData', []);

        $iType = ArrayHelper::getValue($aFormData, 'type', Api::Type_File);
        $sSourceFile = ArrayHelper::getValue($aFormData, 'source_file', '');
        $sSourceStr = ArrayHelper::getValue($aFormData, 'source_str', '');

        $view = new view\UpdFieldsSource([
            'sGroup' => \Yii::t('import', 'head_settings_form'),
            'iType' => $iType,
            'sSourceFile' => $sSourceFile,
            'sSourceStr' => $sSourceStr,
        ]);

        $view->build();
        $this->setInterfaceUpd($view->getInterface());
    }

    /**
     * @param ui\state\BaseInterface $oIface
     *
     * @throws \Exception
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            // Параметр Идентификатора папки загрузки файлов модуля
            '_filebrowser_section' => FileBrowser\Api::getAliasByModule(self::className()),
        ]);
    }

    private function createFolderInRootPath($sPath)
    {
        if (!is_dir(ROOTPATH . $sPath)) {
            if (mkdir(ROOTPATH . $sPath)) {
                chmod(ROOTPATH . $sPath, 0755);
                $this->addMessage(\Yii::t('import', 'folderCreateHeader'), \Yii::t('import', 'folderCreate') . ' ' . $sPath);

                return true;
            }
            $this->addMessage(\Yii::t('import', 'folderCreateHeader'), \Yii::t('import', 'folderNonCreate') . ' ' . $sPath);

            return false;
        }

        return true;
    }

    /**
     * Настройки уведомлнений о результатах импорта
     */
    public function actionNotifySettingsView()
    {
        $aItems = [
            'info' => \Yii::t('import', 'marks_for_notify_body'),
            'mail_notify_title' => ModulesParams::getByName('import', 'mail_notify_title'),
            'mail_notify_body' => ModulesParams::getByName('import', 'mail_notify_body'),
            'mail_notify_mail_to' => ModulesParams::getByName('import', 'mail_notify_mail_to'),
            'mail_notify_is_send' => ModulesParams::getByName('import', 'mail_notify_is_send'),
        ];

        $this->render(new view\NotifySettings(['items' => $aItems]));
    }

    /**
     * Сохранение настроек уведомления о результатах импорта
     */
    public function actionNotifySettingsSave()
    {
        $aData = $this->get('data');

        $sMailTitle = ArrayHelper::getValue($aData, 'mail_notify_title', '');
        $sMailBody = ArrayHelper::getValue($aData, 'mail_notify_body', '');
        $sMailTo = ArrayHelper::getValue($aData, 'mail_notify_mail_to', '');
        $isSend = ArrayHelper::getValue($aData, 'mail_notify_is_send', '');

        ModulesParams::setParams('import', 'mail_notify_title', \Yii::$app->language, $sMailTitle);
        ModulesParams::setParams('import', 'mail_notify_body', \Yii::$app->language, $sMailBody);
        ModulesParams::setParams('import', 'mail_notify_mail_to', \Yii::$app->language, $sMailTo);
        ModulesParams::setParams('import', 'mail_notify_is_send', \Yii::$app->language, $isSend);

        $this->actionList();
    }
}
