<?php

namespace skewer\build\Tool\Subscribe;

use skewer\base\SysVar;
use skewer\base\ui\state\BaseInterface;
use skewer\build\Cms\FileBrowser;
use skewer\build\Page\Subscribe\ar\SubscribeMessage;
use skewer\build\Page\Subscribe\ar\SubscribeMessageRow;
use skewer\build\Page\Subscribe\ar\SubscribePosting;
use skewer\build\Page\Subscribe\ar\SubscribeTemplate;
use skewer\build\Page\Subscribe\ar\SubscribeUser;
use skewer\build\Page\Subscribe\ar\SubscribeUserRow;
use skewer\build\Tool;
use skewer\components\ext;
use skewer\components\i18n\Languages;
use skewer\components\i18n\ModulesParams;
use skewer\components\traits\AssembledArrayTrait;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class Module extends Tool\LeftList\ModulePrototype
{
    use AssembledArrayTrait;

    protected $sLanguageFilter = '';
    protected $iOnPage = 30;
    protected $iPage = 0;

    /** @var array Поля настроек */
    protected $aSettingsKeys =
        [
            'mail.mailText',
            'mail.mailTitle',
            'mail.resultTitle',
            'mail.successText',
            'mail.successTitle',
            'mail.errorText',
            'mail.errorTitle',
        ];

    protected function preExecute()
    {
        $this->iPage = $this->getInt('page');
    }

    public function actionInit()
    {
        if (($this->getConfigParam('mode.firstState') == 'user') or ($this->getInnerData('users_mode'))) {
            $this->actionUsers();
        } else {
            $this->actionList();
        }
    }

    /********************************** USER *********************************/

    public function actionUsers($iPage = 0)
    {
        if ($iPage) {
            $this->iPage = $iPage;
        }

        $iCount = SubscribeUser::find()
            ->getCount();

        $aUsers = SubscribeUser::find()
            ->limit($this->iOnPage, $this->iPage * $this->iOnPage)
            ->asArray()
            ->getAll();

        foreach ($aUsers as &$user) {
            if ($user['confirm'] != '1') {
                $user['confirm'] = '0';
            }
        }

        $this->setInnerData('users_mode', 1);
        $this->setInnerData('current_page', $this->iPage);

        $this->render(new Tool\Subscribe\view\Users([
            'bWithConfirmMode' => (SysVar::get('subscribe_mode') == Api::WITH_CONFIRM),
            'aUsers' => $aUsers,
            'iPage' => $this->iPage,
            'iOnPage' => $this->iOnPage,
            'iCount' => $iCount,
            'bFullBtnMode' => ($this->getConfigParam('mode.fullButtons') != false),
        ]));
    }

    /**
     * @throws UserException
     */
    protected function actionSaveFromList()
    {
        $iId = $this->getInDataValInt('id');

        $sFieldName = $this->get('field_name');

        $oRow = SubscribeUser::findOne(['id' => $iId]);
        /** @var SubscribeUserRow $oRow */
        if (!$oRow) {
            throw new UserException("Запись [{$iId}] не найдена");
        }
        $oRow->{$sFieldName} = $this->getInDataVal($sFieldName);

        $oRow->save();

        $iPage = 0;

        if ($this->getInnerData('current_page')) {
            $iPage = $this->getInnerData('current_page');
        }

        $this->actionUsers($iPage);
    }

    /**
     * @return int
     */
    public function actionSettingsForm()
    {
        $this->render(new Tool\Subscribe\view\SettingsForm([
            'aModes' => Api::getModes(),
            'aSubscribeMode' => [
                'mode' => (int) SysVar::get('subscribe_mode'),
                'iLimit' => SysVar::get('subscribe_limit'),
            ],
        ]));

        return psComplete;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function actionSaveSettings()
    {
        $iMode = $this->getInDataValInt('mode');
        $iLimit = $this->getInDataValInt('iLimit');
        $iLimit = $iLimit <= 0 ? 0 : $iLimit;

        SysVar::set('subscribe_limit', $iLimit);

        /**При переходе в режим "с подтверждением" всем старым поставим статус "подтвержден" */
        if ($iMode == Api::WITH_CONFIRM) {
            \Yii::$app->db->createCommand("UPDATE `subscribe_users` SET confirm=1 WHERE confirm=''")->execute();
        }

        SysVar::set('subscribe_mode', $iMode);

        $this->actionList();
    }

    /**
     * @return int
     */
    public function actionEditUser()
    {
        $aData = $this->get('data');
        $iItemId = $aData['id'] ?? false;

        $aItems = [];
        if ($iItemId) {
            $aItems = SubscribeUser::find($iItemId);
        }

        $this->render(new Tool\Subscribe\view\EditUser([
            'aItems' => $aItems,
        ]));

        return psComplete;
    }

    public function actionSaveUser()
    {
        $aData = $this->get('data');

        /*При добавлении пользователя из админки, он будет подтвержденным*/
        if ($aData['id'] == 0) {
            $aData['confirm'] = 1;
        }

        try {
            // проверка входных данных
            if (!isset($aData['email'])) {
                throw new \Exception('email field not found');
            }
            $sEmail = $aData['email'];

            // валидация поля email
            if (!$sEmail) {
                throw new \Exception(\Yii::t('subscribe', 'email_expected'));
            }
            if (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception(\Yii::t('subscribe', 'invalid_email'));
            }
            /** @var SubscribeUserRow $row */
            $row = SubscribeUser::load($aData);
            if ($row) {
                $row->save();
            }

            if (Api::hasErrorLimitSubscribers()) {
                $this->addMessage('Превышен лимит!', \Yii::t('subscribe', 'subscribe_limit_message', ['iLimit' => SysVar::get('subscribe_limit')]), 5000);
            }

            $this->addModuleNoticeReport(\Yii::t('subscribe', 'saving_mailing_user'), "email: {$sEmail}");
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $iPage = 0;

        if ($this->getInnerData('current_page')) {
            $iPage = $this->getInnerData('current_page');
        }

        $this->actionUsers($iPage);
    }

    public function actionDelUser()
    {
        $aData = $this->get('data');

        $iItemId = $aData['id'] ?? false;

        if ($iItemId) {
            SubscribeUser::delete($iItemId);
        }

        $iPage = 0;

        if ($this->getInnerData('current_page')) {
            $iPage = $this->getInnerData('current_page');
        }

        $this->actionUsers($iPage);
        $this->addModuleNoticeReport(\Yii::t('subscribe', 'deleting_mailing_user'), "User: {$aData['email']}");
    }

    /********************************** TEMPLATES *********************************/

    public function actionTemplates()
    {
        $aItems = SubscribeTemplate::find()->getAll();

        $this->render(new Tool\Subscribe\view\Templates([
            'aItems' => $aItems,
        ]));
    }

    /**
     * @return int
     */
    public function actionEditTemplate()
    {
        $aData = $this->get('data');
        $iItemId = $aData['id'] ?? false;

        $info = Api::addTextInfoBlock();

        if ($iItemId) {
            $row = SubscribeTemplate::find($iItemId);
        } else {
            $row = SubscribeTemplate::getNewRow();
        }

        $this->render(new Tool\Subscribe\view\EditTemplate([
            'sTextInfoBlock' => $info['value'],
            'aSubscribeTemplate' => $row,
            'bModeMultiTemplates' => $this->getConfigParam('mode.multiTemplates'),
        ]));

        $this->addModuleNoticeReport(\Yii::t('subscribe', 'saving_template'), "id {$iItemId}");

        return psComplete;
    }

    /**
     * @throws \Exception
     */
    public function actionSaveTemplate()
    {
        $aData = $this->get('data');

        $row = SubscribeTemplate::load($aData);
        if ($row) {
            $row->save();
        }

        $this->addModuleNoticeReport(\Yii::t('subscribe', 'saving_template'), "id {$aData['id']}");
        if (!$this->getConfigParam('mode.multiTemplates')) {
            $this->actionUsers();
        } else {
            $this->actionTemplates();
        }
    }

    public function actionDelTemplate()
    {
        $aData = $this->get('data');

        $iItemId = $aData['id'] ?? false;

        if ($iItemId) {
            SubscribeTemplate::delete($iItemId);
        }

        $this->actionTemplates();
        $this->addModuleNoticeReport(\Yii::t('subscribe', 'deleting_template'), "id {$iItemId}");
    }

    /********************************** SUBSCRIBE *********************************/

    public function actionList($iPage = 0)
    {
        if ($iPage) {
            $this->iPage = $iPage;
        }

        $iCount = SubscribeMessage::find()
            ->getCount();

        $aItems = SubscribeMessage::find()
            ->order('id', 'DESC')
            ->limit($this->iOnPage, $this->iPage * $this->iOnPage)
            ->asArray()
            ->getAll();

        foreach ($aItems as $iKey => $aItem) {
            $aItems[$iKey]['status'] = Api::getStatusName($aItem['status']);
        }

        $this->setInnerData('current_page', $this->iPage);
        $this->setInnerData('users_mode', 0);

        $this->render(new Tool\Subscribe\view\Index([
            'aItems' => $aItems,
            'iOnPage' => $this->iOnPage,
            'iPage' => $this->iPage,
            'iCount' => $iCount,
            'bWithConfirmMode' => (SysVar::get('subscribe_mode') == Api::WITH_CONFIRM),
        ]));
    }

    /**
     * @return int
     */
    public function actionAddSubscribeStep1()
    {
        $aModel = Api::getChangeTemplateInterface();
        $this->render(new Tool\Subscribe\view\AddSubscribeStep1([
            'aModel' => $aModel,
            'sIconNext' => ext\docked\Api::iconEdit,
         ]));

        return psComplete;
    }

    /**
     * @return int
     */
    public function actionAddSubscribeStep2()
    {
        $aData = $this->get('data');
        $iItemId = $aData['tpl'] ?? false;

        $info = Api::addTextInfoBlock();

        $aItems = [];
        $aTempItems = SubscribeTemplate::find()->where('id', $iItemId)->asArray()->getOne();

        $aItems['title'] = $aTempItems['title'] ?? '';
        $aItems['text'] = $aTempItems['content'] ?? '';
        $aItems['status'] = Api::statusFormation;
        $aItems['template'] = $iItemId;

        $this->render(new Tool\Subscribe\view\AddSubscribeStep2([
            'sTextInfoBlock' => $info['value'],
            'aStatus' => Api::getStatusArr(),
            'aItems' => $aItems,
        ]));

        return psComplete;
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function actionEditSubscribe()
    {
        $aData = $this->get('data');
        $iItemId = $aData['id'] ?? false;
        if (!$iItemId) {
            throw new \Exception(\Yii::t('subscribe', 'wrong_subscribe_id'));
        }
        $info = Api::addTextInfoBlock();

        $aTempItems = SubscribeMessage::find()->where('id', $iItemId)->asArray()->getOne();

        $bTypeView = true;
        // блокироум редактирование по статусу
        if (!isset($aTempItems['status']) || $aTempItems['status'] != Api::statusFormation) {
            $bTypeView = false;
        }

        $aTempItems['title'] = $aTempItems['title'] ?? '';
        $aTempItems['text'] = $aTempItems['text'] ?? '';
        $aTempItems['template'] = $iItemId;

        $this->render(new Tool\Subscribe\view\EditSubscribe([
            'sTextInfoBlock' => $info['value'],
            'bTypeView' => $bTypeView,
            'aStatus' => Api::getStatusArr(),
            'aTempItems' => $aTempItems,
            'iStatusFormation' => Api::statusFormation,
        ]));

        return psComplete;
    }

    /**
     * @throws \Exception
     */
    public function actionSaveSubscribe()
    {
        $aData = $this->get('data');

        /** @var SubscribeMessageRow $row */
        $row = SubscribeMessage::load($aData);
        if ($row) {
            $row->status = Api::statusFormation;
            $row->save();
        }

        $this->addModuleNoticeReport(\Yii::t('subscribe', 'saving_mailing'), "id {$row->id}");
        $iPage = 0;

        if ($this->getInnerData('current_page')) {
            $iPage = $this->getInnerData('current_page');
        }

        $this->actionList($iPage);
    }

    public function actionDelSubscribe()
    {
        $aData = $this->get('data');
        $iItemId = $aData['id'] ?? false;

        if ($iItemId) {
            SubscribePosting::delete()->where('id_body', $iItemId)->get();
            SubscribeMessage::delete($iItemId);
        }

        $this->addModuleNoticeReport(\Yii::t('subscribe', 'deleting_mailing'), "id {$iItemId}");
        $iPage = 0;

        if ($this->getInnerData('current_page')) {
            $iPage = $this->getInnerData('current_page');
        }

        $this->actionList($iPage);
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function actionSendSubscribeForm()
    {
        $aData = $this->get('data');
        $iItemId = $aData['id'] ?? false;
        if (!$iItemId) {
            throw new \Exception(\Yii::t('subscribe', 'wrong_subscribe_id'));
        }
        $aTempItems = SubscribeMessage::find()->where('id', $iItemId)->asArray()->getOne();
        $aTempItems['title'] = $aTempItems['title'] ?? '';
        $aTempItems['text'] = $aTempItems['text'] ?? '';

        $this->render(new Tool\Subscribe\view\SendSubscribeForm([
            'aTempItems' => $aTempItems,
        ]));

        return psComplete;
    }

    /**
     * @return int
     */
    public function actionSendSubscribe()
    {
        try {
            $aData = $this->get('data');

            $iItemId = $aData['id'] ?? false;

            if (!$iItemId) {
                throw new \Exception(\Yii::t('subscribe', 'wrong_message_id'));
            }
            $iSubId = Api::addMailer($iItemId);
            if (!$iSubId) {
                throw new \Exception(\Yii::t('subscribe', 'not_added'));
            }
            /** @var SubscribeMessageRow $row */
            $row = SubscribeMessage::find($iItemId);
            if ($row) {
                $row->status = Api::statusWaiting;
                $row->save();
            }

            Api::makeTask($iSubId);

            $this->addMessage(\Yii::t('subscribe', 'mailing_created'));
            $this->addModuleNoticeReport(\Yii::t('subscribe', 'log_create_mailing'));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();

        return psComplete;
    }

    /**
     * @throws UserException
     * @throws \Exception
     *
     * @return int
     */
    public function actionSendToEmailSubscribe()
    {
        $aData = $this->get('data');

        $iItemId = $aData['id'] ?? false;
        $sTestMail = $aData['test_mail'] ?? false;

        if (!$iItemId) {
            throw new UserException(\Yii::t('subscribe', 'wrong_message_id'));
        }
        if (!$sTestMail) {
            throw new UserException(\Yii::t('subscribe', 'email_expected'));
        }
        if (!Api::sendTestMailer($iItemId, $sTestMail)) {
            throw new \Exception(\Yii::t('subscribe', 'test_messages_fail'));
        }
        $this->addMessage(\Yii::t('subscribe', 'test_email_sended'));
        $this->addModuleNoticeReport(\Yii::t('subscribe', 'test_email_sended'));

        $this->actionList();

        return psComplete;
    }

    /**
     * Форма настроек модуля.
     */
    protected function actionSettings()
    {
        $this->sLanguageFilter = $this->get('filter_language', \Yii::$app->i18n->getTranslateLanguage());

        $aLanguages = Languages::getAllActive();
        $aLanguages = ArrayHelper::map($aLanguages, 'name', 'title');

        $aModulesData = ModulesParams::getByModule('subscribe', $this->sLanguageFilter);
        $this->setInnerData('languageFilter', $this->sLanguageFilter);

        foreach ($this->aSettingsKeys as  $key) {
            $aItems[$key] = (isset($aModulesData[$key])) ? $aModulesData[$key] : '';
        }

        $aItems['info'] = \Yii::t(
            'subscribe',
            'head_mail_text',
            [\Yii::t('app', 'site_label', [], $this->sLanguageFilter),
                \Yii::t('app', 'url_label', [], $this->sLanguageFilter),
                \Yii::t('review', 'label_user', [], $this->sLanguageFilter), $this->sLanguageFilter, ],
            $this->sLanguageFilter
        );

        $this->render(new Tool\Subscribe\view\Settings([
            'aLanguages' => $aLanguages,
            'sLanguageFilter' => $this->sLanguageFilter,
            'aItems' => $aItems,
        ]));
    }

    /**
     * Сохраняем настройки формы.
     */
    protected function actionSaveMessageSettings()
    {
        $aData = $this->getAssembleArray($this->getInData());

        $sLanguage = $this->getInnerData('languageFilter');
        $this->setInnerData('languageFilter', '');

        if ($sLanguage) {
            foreach ($aData as $sName => $sValue) {
                if (!in_array($sName, $this->aSettingsKeys)) {
                    continue;
                }

                ModulesParams::setParams('subscribe', $sName, $sLanguage, $sValue);
            }
        }

        $this->actionInit();
    }

    /***************************Импорт подписчиков******************************/

    /**
     * Показ формы выбора источника для импорта.
     */
    protected function actionImportFormStep1()
    {
        $this->render(new Tool\Subscribe\view\ImportFormStep1([]));
    }

    /**
     * Форма импорта.
     */
    protected function actionImportForm()
    {
        $sMode = $this->getInDataVal('mode', 'text');

        $this->setInnerData('mode', $sMode);

        $this->render(new view\ImportFormSave([
            'mode' => $sMode,
        ]));
    }

    /*
     * импорт
     */
    protected function actionImport()
    {
        $sMode = $this->getInnerData('mode');

        $sClassName = 'skewer\build\Tool\Subscribe\import\Type' . mb_strtoupper($sMode);
        /** @var \skewer\build\Tool\Subscribe\import\Prototype $oProvider */
        $oProvider = new $sClassName();

        $oProvider->validate($this->getInData());

        $oProvider->import($this->getInData());

        $this->addMessage(\Yii::t('subscribe', 'import_result'), \Yii::t(
            'subscribe',
            'import_result_text',
            [
            'iCount' => $oProvider->iCount,
            'iSuccess' => $oProvider->iSuccess,
            'iFailed' => $oProvider->iFailed,
            ]
        ));

        $aData['import_result'] = \Yii::$app->getView()->renderFile(
            __DIR__ . '/templates/import_results.php',
            [
            'results' => \Yii::t(
                'subscribe',
                'import_result_text',
                [
                    'iCount' => $oProvider->iCount,
                    'iSuccess' => $oProvider->iSuccess,
                    'iFailed' => $oProvider->iFailed,
                ]
            ),
            'log' => $oProvider->aLog,
            'sLimitHasError' => Api::hasErrorLimitSubscribers() ? \Yii::t('subscribe', 'subscribe_limit_message', ['iLimit' => SysVar::get('subscribe_limit')]) : '',
            ]
        );

        $this->render(new Tool\Subscribe\view\Import([
            'aData' => $aData,
        ]));
    }

    /*****************Экспорт подписчиков******************/
    protected function actionExportForm()
    {
        $this->render(new Tool\Subscribe\view\ExportForm([]));
    }

    protected function actionExport()
    {
        $sMode = $this->getInDataVal('mode');

        $sClassName = 'skewer\build\Tool\Subscribe\import\Type' . mb_strtoupper($sMode);
        /** @var \skewer\build\Tool\Subscribe\import\Prototype $oProvider */
        $oProvider = new $sClassName();

        $sFileHash = $oProvider->export($sMode);

        $aParams = [
            'file_hash' => $sFileHash,
            'mode' => $sMode,
        ];

        $this->render(new Tool\Subscribe\view\Export([
            'aParams' => $aParams,
        ]));
    }

    /**
     * @param BaseInterface $oIface
     *
     * @throws \Exception
     */
    protected function setServiceData(BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            // Параметр Идентификатора папки загрузки файлов модуля
            '_filebrowser_section' => FileBrowser\Api::getAliasByModule(self::className()),
        ]);
    }
}//class
