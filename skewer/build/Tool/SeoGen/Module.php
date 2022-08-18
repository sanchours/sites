<?php

namespace skewer\build\Tool\SeoGen;

use skewer\base\ui\state\BaseInterface;
use skewer\build\Cms\FileBrowser;
use skewer\build\Tool;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Модуль импорта/экспорта seo данных
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $this->render(new view\Index([]));
    }

    public function actionUpdateImportState()
    {
        $aData = $this->get('formData', []);
        $sTypeData = ArrayHelper::getValue($aData, 'data_type', '');

        $this->render(
            new view\ImportFormSettings($this, [
                'sTypeData' => $sTypeData,
                'aValues' => $aData,
            ])
        );
    }

    protected function actionImportState()
    {
        $this->setInnerData('state', 'importState');

        $this->render(
            new view\ImportFormSettings($this)
        );
    }

    protected function actionExportState()
    {
        $this->setInnerData('state', 'exportState');

        $this->render(
            new view\ExportFormSettings()
        );
    }

    public function actionUpdateExportState()
    {
        $aData = $this->get('formData', []);
        $sTypeData = ArrayHelper::getValue($aData, 'data_type', '');

        $this->render(
            new view\ExportFormSettings([
                'sTypeData' => $sTypeData,
                'aValues' => $aData,
            ])
        );
    }

    /**
     * @throws UserException
     * @throws \yii\db\Exception
     */
    public function actionShowLastLog()
    {
        $sState = $this->getInnerData('state');

        switch ($sState) {
            case 'exportState':
                $sClass = ExportTask::className();
                break;
            case 'importState':
                $sClass = ImportTask::className();
                break;
            default:
                throw new UserException('Неизвестный тип лога');
        }

        // Берём лог последней задачи заданного класса
        $aTask = \Yii::$app->db->createCommand('
            SELECT * 
            FROM `task` 
            WHERE `class` = :className ORDER BY `upd_time` DESC 
        ', ['className' => $sClass])->queryOne();

        if (!$aTask) {
            throw new UserException('Нет данных по данной операции');
        }
        $this->showLog($aTask['id']);
    }

    /**
     * @throws UserException
     */
    protected function actionExportRun()
    {
        if (defined('YII_DEBUG') && YII_DEBUG || defined('YII_ENV') && YII_ENV == 'dev') {
            throw new UserException('Перед запуском экспорта выключите дебаг-режим!');
        }

        $aData = $this->get('data', []);

        /* Валидация пришедших данных */

        if (!$sDataType = ArrayHelper::getValue($aData, 'data_type', '')) {
            throw new UserException('Не выбран тип данных');
        }
        $oExporter = Tool\SeoGen\exporter\Api::getExporterByAlias($sDataType);

        if (!$oExporter) {
            throw new UserException("Неизвестный тип экспортера [{$sDataType}]");
        }

        $aErrors = [];
        if ($oExporter->validateParams($aData, $aErrors) === false) {
            throw new UserException(reset($aErrors));
        }

        $this->actionRepeatExportRun($aData);
    }

    /**
     * @param array $aParam
     *
     * @throws UserException
     */
    protected function actionRepeatExportRun($aParam = [])
    {
        $aTask = $this->runTaskWithReboot(ExportTask::getConfig($aParam), 'repeatExportRun');

        $this->showLog($aTask['id']);
    }

    /**
     * @throws UserException
     */
    protected function actionImportRun()
    {
        if (defined('YII_DEBUG') && YII_DEBUG || defined('YII_ENV') && YII_ENV == 'dev') {
            throw new UserException('Перед запуском импорта выключите дебаг-режим!');
        }

        $aData = $this->get('data');

        /* Валидация пришедших данных */

        if (!$sDataType = ArrayHelper::getValue($aData, 'data_type', '')) {
            throw new UserException('Не выбран тип данных');
        }
        $oImporter = Tool\SeoGen\importer\Api::getImporterByAlias($sDataType);

        if (!$oImporter) {
            throw new UserException("Неизвестый тип загрузчика [{$sDataType}]");
        }

        $aErrors = [];
        if ($oImporter->validateParams($aData, $aErrors) === false) {
            throw new UserException(reset($aErrors));
        }

        $this->actionRepeatImportRun($aData);
    }

    /**
     * @param array $aParam
     *
     * @throws UserException
     */
    public function actionRepeatImportRun($aParam = [])
    {
        $aTask = $this->runTaskWithReboot(ImportTask::getConfig($aParam), 'repeatImportRun');

        $this->showLog($aTask['id']);
    }

    /** Выводить лог. Работает и для импорта и для экспорта
     * @param  int $iTaskId - id задачи соответствующей логу
     *
     * @throws UserException
     */
    private function showLog($iTaskId)
    {
        if (!$iTaskId) {
            throw new UserException(\Yii::t('seoGen', 'error_task_not_fount'));
        }

        $aLogParams = \skewer\components\import\Api::getLog($iTaskId);

        if (isset($aLogParams['status'])) {
            $aLogParams['status'] = Tool\Import\View::getStatus($aLogParams);
        }

        $baskAction = $this->getInnerData('state');

        $sText = \Yii::$app->view->renderPhpFile(
            __DIR__ . \DIRECTORY_SEPARATOR . $this->getTplDirectory() . \DIRECTORY_SEPARATOR . 'log_template.php',
            ['aLogParams' => $aLogParams]
        );

        $this->render(new view\ShowLog([
            'baskAction' => $baskAction,
            'text' => $sText,
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
}
