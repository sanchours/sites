<?php

namespace skewer\build\Design\CSSTransfer;

use skewer\base\SysVar;
use skewer\build\Cms;
use skewer\components\ext;
use yii\base\Exception;

class Module extends Cms\Tabs\ModulePrototype
{
    /**
     * Состояние начальное.
     */
    protected function actionInit()
    {
        $oForm = new ext\FormView();

        $aItems = [];

        $oForm->addExtButton(ext\docked\Api::create('Собрать изменения')
            ->setAction('getModificationsForm'));

        $oForm->addExtButton(ext\docked\Api::create('Импортировать CSS параметры')
            ->setAction('importModificationsForm'));

        $oForm->addExtButton(ext\docked\Api::create('Импортировать CSS редактор')
            ->setAction('importCSSForm'));

        $oForm->addExtButton(ext\docked\Api::create('История изменений')
            ->setAction('getHistory'));

        $oForm->addExtButton(ext\docked\Api::create('Настройки')
            ->setAction('getSettingsForm'));

        $oForm->setFields($aItems);
        $oForm->setValues([]);

        $this->setInterface($oForm);
    }

    /**
     * Сохранение настроек.
     */
    protected function actionSaveSettingsForm()
    {
        SysVar::set('CSSTransfer.OverlayValues', $this->getInDataVal('OverlayValues'));

        $this->actionInit();
    }

    /**
     * Форма настроек.
     */
    protected function actionGetSettingsForm()
    {
        $oForm = new ext\FormView();

        $aItems = [];

        $aItems['OverlayValues'] = [
            'name' => 'OverlayValues',
            'title' => 'Переписывать файлы',
            'view' => 'check',
            'value' => (bool) SysVar::get('CSSTransfer.OverlayValues'),
        ];

        $oForm->addExtButton(ext\docked\Api::create('Сохранить')
            ->setAction('saveSettingsForm')->unsetDirtyChecker());

        $oForm->addExtButton(ext\docked\Api::create('Назад')
            ->setAction('init')
            ->setIconCls(ext\docked\Api::iconCancel)
            ->unsetDirtyChecker());

        $oForm->setFields($aItems);
        $oForm->setValues([]);

        $this->setInterface($oForm);
    }

    /**
     * Лог истории изменений.
     */
    protected function actionGetHistory()
    {
        $oForm = new ext\FormView();
        $sHistory = Api::getCssHistory();

        $aItems = [];

        $aItems['history'] = [
            'name' => 'history',
            'title' => 'История изменений',
            'view' => 'text',
            'value' => $sHistory,
            'height' => '100%',
        ];

        $oForm->addExtButton(ext\docked\Api::create('Назад')
            ->setAction('init')
            ->setIconCls(ext\docked\Api::iconCancel)
            ->unsetDirtyChecker());

        $oForm->setFields($aItems);
        $oForm->setValues([]);

        $this->setInterface($oForm);
    }

    /**
     * Форма сборки файла модификаций.
     */
    protected function actionGetModificationsForm()
    {
        $oForm = new ext\FormView();

        $aItems = [];

        $aItems['modification_time_start'] = [
            'name' => 'modification_time_start',
            'title' => 'Дата модификации (с даты)',
            'view' => 'datetime',
            'value' => date('Y-m-d ') . '00:00:00',
        ];

        $aItems['modification_time_end'] = [
            'name' => 'modification_time_end',
            'title' => 'Дата модификации (по дату)',
            'view' => 'datetime',
            'value' => date('Y-m-d ') . '00:00:00',
        ];

        $oForm->addExtButton(ext\docked\Api::create('Сформировать файл')
            ->setAction('getModifications')->unsetDirtyChecker());

        $oForm->addExtButton(ext\docked\Api::create('Назад')
            ->setAction('init')
            ->setIconCls(ext\docked\Api::iconCancel)
            ->unsetDirtyChecker());

        $oForm->setFields($aItems);
        $oForm->setValues([]);

        $this->setInterface($oForm);
    }

    /**
     * Сборка модификаций.
     *
     * @throws Exception
     */
    protected function actionGetModifications()
    {
        $sModificationTimeStart = $this->getInDataVal('modification_time_start');

        if (!$sModificationTimeStart) {
            throw new Exception('Invalid modification_time_start');
        }
        if (strtotime($sModificationTimeStart) > time()) {
            throw new Exception('Invalid modification_time_start');
        }
        $sModificationTimeEnd = $this->getInDataVal('modification_time_end');

        if (!$sModificationTimeEnd) {
            throw new Exception('Invalid modification_time_end');
        }
        if ($sModificationTimeEnd == $sModificationTimeStart) {
            throw new Exception('Неверный временной интервал');
        }
        try {
            $sText = Api::getModified($sModificationTimeStart, $sModificationTimeEnd);

            $oForm = new ext\FormView();

            $oForm->setAddText($sText);

            $oForm->addExtButton(ext\docked\Api::create('Назад')
                ->setAction('getModificationsForm')
                ->setIconCls(ext\docked\Api::iconCancel)
                ->unsetDirtyChecker());

            $this->setInterface($oForm);
        } catch (Exception $e) {
            $this->addError('Error', $e);
        }
    }

    /**
     * Форма импорта модификаций.
     */
    protected function actionImportModificationsForm()
    {
        $oForm = new ext\FormView();

        $aItems = [];

        $aItems['csv_file'] = [
            'name' => 'csv_file',
            'title' => 'Путь к фалу с изменениями',
            'view' => 'str',
        ];

        $oForm->addExtButton(ext\docked\Api::create('Импортировать данные')
            ->setAction('importModifications')->unsetDirtyChecker());

        $oForm->addExtButton(ext\docked\Api::create('Назад')
            ->setAction('init')
            ->setIconCls(ext\docked\Api::iconCancel)
            ->unsetDirtyChecker());

        $oForm->setFields($aItems);
        $oForm->setValues([]);

        $this->setInterface($oForm);
    }

    /**
     * Форма импорта редактора.
     */
    protected function actionImportCSSForm()
    {
        $oForm = new ext\FormView();

        $aItems = [];

        $aItems['json_file'] = [
            'name' => 'json_file',
            'title' => 'Путь к фалу с изменениями',
            'view' => 'str',
        ];

        $oForm->addExtButton(ext\docked\Api::create('Импортировать данные')
            ->setAction('importCSS')->unsetDirtyChecker());

        $oForm->addExtButton(ext\docked\Api::create('Назад')
            ->setAction('init')
            ->setIconCls(ext\docked\Api::iconCancel)
            ->unsetDirtyChecker());

        $oForm->setFields($aItems);
        $oForm->setValues([]);

        $this->setInterface($oForm);
    }

    /**
     * Импорт модификаций.
     *
     * @throws Exception
     */
    protected function actionImportModifications()
    {
        $sFileName = $this->getInDataVal('csv_file');

        $sFileName = trim($sFileName);

        $aParsedUrl = parse_url($sFileName);

        if (!$sFileName || !isset($aParsedUrl['host']) || !isset($aParsedUrl['scheme']) || !isset($aParsedUrl['path'])) {
            throw new Exception('Некорректный url');
        }

        if (!in_array(Api::getHTTPCode($sFileName), ['200', '301', '302', '304'])) {
            throw new Exception('Файл не существует');
        }

        if (($sContent = @file_get_contents($sFileName)) === false) {
            throw new Exception('Не могу подгрузить данные из ' . $sFileName);
        }

        file_put_contents(WEBPATH . '/files/input_css.csv', $sContent);

        $sTestUrl = $aParsedUrl['host'];

        if (!$sTestUrl) {
            throw new Exception('Некорректный url');
        }

        try {
            $sText = Api::applyCssUpdate(WEBPATH . '/files/input_css.csv', $sTestUrl);
            $oForm = new ext\FormView();

            $oForm->setAddText($sText);

            $oForm->addExtButton(ext\docked\Api::create('Назад')
                ->setAction('importModificationsForm')
                ->setIconCls(ext\docked\Api::iconCancel)
                ->unsetDirtyChecker());

            $this->setInterface($oForm);
        } catch (Exception $e) {
            $this->addError('Error', $e);
        }

        unlink(WEBPATH . '/files/input_css.csv');

        $this->fireJSEvent('reload_show_frame');
        \Yii::$app->clearAssets();
    }

    /**
     * Импорт редактора.
     *
     * @throws Exception
     */
    protected function actionImportCSS()
    {
        $sFileName = $this->getInDataVal('json_file');

        $sFileName = trim($sFileName);

        $aParsedUrl = parse_url($sFileName);

        if (!$sFileName || !isset($aParsedUrl['host']) || !isset($aParsedUrl['scheme']) || !isset($aParsedUrl['path'])) {
            throw new Exception('Некорректный url');
        }

        if (!in_array(Api::getHTTPCode($sFileName), ['200', '301', '302', '304'])) {
            throw new Exception('Файл не существует');
        }

        if (($sContent = @file_get_contents($sFileName)) === false) {
            throw new Exception('Не могу подгрузить данные из ' . $sFileName);
        }

        try {
            $sText = Api::addCSSBlock($sContent);
            $oForm = new ext\FormView();

            $oForm->setAddText($sText);

            $oForm->addExtButton(ext\docked\Api::create('Назад')
                ->setAction('importCSSForm')
                ->setIconCls(ext\docked\Api::iconCancel)
                ->unsetDirtyChecker());

            $this->setInterface($oForm);
        } catch (Exception $e) {
            $this->addError('Error', $e);
        }

        \skewer\build\Design\CSSEditor\Module::rebuildCssSettings();

        $this->fireJSEvent('reload_show_frame');
        $this->fireJSEvent('reload_display_form');
        \Yii::$app->clearAssets();
    }
}
