<?php

namespace skewer\build\Tool\Backup;

use skewer\base\queue\ar\Task;
use skewer\base\queue as QM;
use skewer\base\site\Server;
use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;
use skewer\components\ext;
use skewer\helpers\Files;
use yii\base\Exception;
use yii\base\UserException;

class Module extends Tool\LeftList\ModulePrototype
{
    public function getTitle()
    {
        return self::getTitleTree();
    }

    /**
     * Возвращает название модуля для левой колонки админки.
     *
     * @return mixed
     */
    public static function getTitleTree()
    {
        if (INCLUSTER) {
            return \Yii::t('backup', 'tab_name');
        }

        return \Yii::t('backup', 'tab_name_outcluster');
    }

    protected function preExecute()
    {
        CurrentAdmin::testControlPanelAccess();
    }

    public function createFormIncluster()
    {
        // добавление набора данных
        $aItems = Api::getListItems();

        // форматирование элементов
        if (isset($aItems['items']) and count($aItems['items'])) {
            foreach ($aItems['items'] as $iKey => $aItem) {
                $aItem['size_sort'] = Files::sizeToSortStr($aItem['size']);
                $aItem['size'] = Files::sizeToStr($aItem['size']);
                $aItems['items'][$iKey] = $aItem;
            }
        }

        $this->render(new Tool\Backup\view\FormIncluster([
            'aItems' => $aItems['items'],
            'bIsApache' => Server::isApache(),
        ]));
    }

    public function createFormNotcluster()
    {
        if (!is_dir(ROOTPATH . 'backup')) {
            mkdir(ROOTPATH . 'backup');
        }

        $aValues = [];
        $aFiles = Api::getDumpFiles(ROOTPATH . '/backup');

        rsort($aFiles);

        foreach ($aFiles as $file) {
            $aValues[] = ['filename' => $file['filename'], 'filename_text' => '<a href="/local/?ctrl=' . $this->getModuleName() . '&&fileName=' . $file['filename'] . '">' . $file['filename'] . '</a>', 'filesize' => $file['filesize']];
        }

        $this->render(new Tool\Backup\view\FormNotCluster([
            'aValues' => $aValues,
        ]));
    }

    public function actionDeleteBackupDB()
    {
        $data = $this->getInData();
        if (isset($data['filename'])) {
            unlink(ROOTPATH . 'backup/' . $data['filename']);
        }
        $this->actionInit();
    }

    public function actionRestoreBackupDB()
    {
        $data = $this->getInData();
        if (isset($data['filename']) && is_file(ROOTPATH . 'backup/' . $data['filename'])) {
            $bResult = Api::restoreDBase(ROOTPATH . 'backup/' . $data['filename']);

            if (!$bResult) {
                $this->addError(\Yii::t('Backup', 'recovery_error_db'));
            }

            // сброс css и языков - они зависят от базы
            \Yii::$app->clearAssets();
            \Yii::$app->clearLang();

            $this->addMessage(\Yii::t('backup', 'backupOk'));
        }

        $this->actionInit();
    }

    public function actionAddBackupDB()
    {
        Api::createDBbackup(ROOTPATH . 'backup/' . date('Y-m-d_H-i-s') . '.sql');

        $this->actionInit();
    }

    public function actionInit()
    {
        if (INCLUSTER) {
            $this->createFormIncluster();
        } else {
            $this->createFormNotcluster();
        }
    }

    public function actionToolsForm()
    {
        $aData = Service::getBackupSetting();

        $aItems = [];

        /* Файл */
        $aItems['bs_enable'] = [
            'name' => 'bs_enable',
            'title' => \Yii::t('backup', 'useLocalSettings'),
            'view' => 'check',
            'value' => $aData['bs_enable'],
            //'disabled' => true,
        ];

        $aItems['bs_day'] = [
            'name' => 'bs_day',
            'title' => \Yii::t('backup', 'bs_day'),
            'view' => 'int',
            'value' => $aData['bs_day'],
            //'disabled' => true,
        ];

        $aItems['bs_week'] = [
            'name' => 'bs_week',
            'title' => \Yii::t('backup', 'bs_week'),
            'view' => 'int',
            'value' => $aData['bs_week'],
            //'disabled' => true,
        ];

        $aItems['bs_month'] = [
            'name' => 'bs_month',
            'title' => \Yii::t('backup', 'bs_month'),
            'view' => 'int',
            'value' => $aData['bs_month'],
            //'disabled' => true,
        ];
        /*
        $aItems['bs_hour'] = array(
            'name' => 'bs_hour',
            'title' => 'Время запуска, час',
            'view' => 'int',
            'value' => $aData['bs_hour'],
            //'disabled' => true,
        );

        $aItems['bs_min'] = array(
            'name' => 'bs_min',
            'title' => 'Время запуска, мин',
            'view' => 'int',
            'value' => $aData['bs_min'],
            //'disabled' => true,
        );
                            */
        $this->render(new Tool\Backup\view\ToolsForm([
            'aItems' => $aItems,
        ]));

        return psComplete;
    }

    public function actionSaveTools()
    {
        $aData = $this->get('data');

        if ($aData['bs_day'] < 0 || $aData['bs_week'] < 0 || $aData['bs_month'] < 0) {
            throw new UserException(\Yii::t('backup', 'invalid_data'));
        }
        Api::setBackupSetting($aData);

        $this->actionToolsForm();

        return psComplete;
    }

    public function actionCreateBackupForm()
    {
        $aItems = [];

        $aItems['comment'] = [
            'name' => 'comment',
            'title' => \Yii::t('backup', 'comment'),
            'view' => 'text',
            'value' => '',
        ];

        $this->render(new Tool\Backup\view\BackupForm([
            'aItems' => $aItems,
        ]));

        return psComplete;
    }

    public function actionCreateBackup()
    {
        /*Проверяем наличие параллельной задачи на бекапирование*/
        if (Api::hasBackupTask()) {
            throw new Exception(\Yii::t('backup', 'backup_task_in_process'));
        }
        $aData = $this->getInData();

        $sComment = \Yii::t('backup', 'backup_default_comment');

        if (isset($aData['comment']) && $aData['comment']) {
            $sComment = $aData['comment'];
        }

        QM\Manager::clear();

        $aRes = Api::createNewBackup($sComment);
        $iStatus = $aRes['status'];

        if ($iStatus == QM\Task::stClose) {
            $iStatus = QM\Task::stComplete;
        }
        $aStatus = QM\Api::getStatusList();
        $status = (isset($aStatus[$iStatus])) ? $aStatus[$iStatus] : '';

        $this->addMessage(\Yii::t('backup', 'backupStatus') . ': ' . $status);
        $this->addModuleNoticeReport(\Yii::t('backup', 'addBackupReport'));

        $this->actionInit();
    }

    public function actionRecoverForm()
    {
        $aData = $this->get('data');

        try {
            Api::checkBackup($aData['id']);
        } catch (\Exception $e) {
            $oForm = new ext\ShowView();

            $oForm->setAddText(\Yii::t('backup', 'error_msg', $e->getMessage()));

            $oForm->addBtnCancel('init');
            $oForm->addBtnSeparator('->');

            $this->setInterface($oForm);

            return psComplete;
        }

        $this->setPanelName(\Yii::t('backup', 'restoreMaster'), true);

        /* Id резервной копии */
        $aItems['id'] = [
            'name' => 'id',
            'title' => '',
            'view' => 'hide',
            'value' => $aData['id'],
            'disabled' => false,
        ];

        /* Файл */
        $aItems['file'] = [
            'name' => 'file',
            'title' => \Yii::t('backup', 'file'),
            'view' => 'str',
            'value' => $aData['backup_file'],
            'disabled' => true,
        ];

        /* Дата создания */
        $aItems['creation_date'] = [
            'name' => 'creation_date',
            'title' => \Yii::t('backup', 'creation_date'),
            'view' => 'str',
            'value' => $aData['date'],
            'disabled' => true,
        ];

        /* Делать ли резервную копию перед разворачиванием площадки */
        $aItems['before_backup'] = [
            'name' => 'before_backup',
            'title' => \Yii::t('backup', 'beforeBackup'),
            'view' => 'check',
            'value' => 1,
        ];

        $this->render(new Tool\Backup\view\RecoverForm([
            'aItems' => $aItems,
        ]));

        return psComplete;
    }

    public function actionRecover()
    {
        try {
            $aData = $this->get('data');

            if (!isset($aData['id']) or !$iBackupId = $aData['id']) {
                throw new \Exception('Recover error: Backup is undefined!');
            }
            $bCreateBeforeBackup = (isset($aData['before_backup']) and $aData['before_backup']) ? true : false;

            /* Получить данные по резервной копии */
            if ($bCreateBeforeBackup) {
                //$mError = false;
                //$sDescription = 'Создано перед восстановлением из резервной копии от '.$aBackupItem['date'];
                //if(!$this->createBackup($aBackupItem['site_id'], 3, $sDescription, $mError)) throw new Exception($mError);
                Api::createNewBackup();
            }

            //$mError = false;
            //if(!$this->recoverBackup($aSiteItem['name'], $aBackupItem['backup_file'], $mError)) throw new Exception($mError);
            Api::recoverBackup([$iBackupId]);

            // стираем старые таски. при восстановлении может произойти рассинхронизация.
            Task::delete()->get();

            $this->addMessage(\Yii::t('backup', 'backupOk'));
            $this->addModuleNoticeReport(\Yii::t('backup', 'goodRecover'));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionInit();

        return psComplete;
    }

    public function actionRemove()
    {
        $aData = $this->get('data');

        Api::removeBackup($aData);
        $this->addModuleNoticeReport(\Yii::t('backup', 'deleteBackup'));
        $this->actionInit();

        return psComplete;
    }

//    public function actionDownloadFile(){
//
//        $aData = $this->get('data');
//
//        $sToken = Service::getDownloadFileToken($aData);
//
//        if(!$sToken) throw new \Exception(\Yii::t('backup', 'loadBackupError'));
//
//        $sLink = str_replace('index','downloadBackup',CLUSTERGATEWAY);
//        $sLink .= '?token='.$sToken;
//
//        $this->setData('link',$sLink);
//
//        // дополнительная библиотека для отображения
//        $this->addLibClass( 'BackupFile' );
//        $oInterface = new \ExtUserFile( 'BackupFile' );
//        $this->setInterface( $oInterface );
//
//        return psComplete;
//    }
}
