<?php

namespace skewer\build\Tool\ImportContent;

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
        $this->setInnerData('state', 'importState');

        $this->render(
            new view\ImportFormSettings($this)
        );
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

    /**
     * @throws UserException
     * @throws \yii\db\Exception
     */
    public function actionShowLastLog()
    {
        $sClass = ImportTask::className();

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
    protected function actionImportRun()
    {
        $aData = $this->get('data');

        /* Валидация пришедших данных */

        if (!$sFile = $this->getInDataVal('file', '')) {
            throw new UserException('Не загружен файл');
        }
        if (!preg_match('{[^\.]\.(xls|xlsx)$}i', $sFile)) {
            throw new UserException('Загрузите файл с расширением [.xls|xlsx]');
        }
        if (!$sDataType = ArrayHelper::getValue($aData, 'data_type', '')) {
            throw new UserException('Не выбран тип данных');
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

        $sText = \Yii::$app->view->renderPhpFile(
            __DIR__ . \DIRECTORY_SEPARATOR . $this->getTplDirectory() . \DIRECTORY_SEPARATOR . 'log_template.php',
            ['aLogParams' => $aLogParams]
        );

        $baskAction = $this->getInnerData('state');

        $this->render(new view\ShowLog([
            'text' => $sText,
            'backAction' => $baskAction,
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
