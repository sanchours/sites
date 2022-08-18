<?php

namespace skewer\build\Design\CSSEditor;

use skewer\base\ui;
use skewer\build\Cms;
use skewer\build\Design\CSSEditor\models\CssFiles;
use skewer\build\Design\CSSEditor\view\Export;
use skewer\build\Design\CSSEditor\view\Index;
use skewer\components\design\CssParser;
use skewer\components\design\Design;
use skewer\components\ext;
use yii\base\UserException;

/**
 * Модуль работы с редактором пользовательского css кода.
 */
class Module extends Cms\Tabs\ModulePrototype
{
    protected $name = 'CSS Редактор';

    /** @var string режим отображения (по-умолчанию или pda версия) */
    protected $sViewMode = Design::versionDefault;

    /**
     * Пользовательская функция инициализации модуля.
     */
    protected function onCreate()
    {
        $this->addJSListener('urlChange', 'checkVersion');
    }

    protected function actionCheckVersion()
    {
        // получить тип отображения
        list($sUrl) = $this->get('params');
        if (!$sUrl) {
            throw new \Exception('Url не задан');
        }
        $sType = Design::getVersionByUrl($sUrl);

        // если не совпадает с текущим
        if ($sType !== $this->sViewMode) {
            $this->sViewMode = $sType;
            $this->actionInit();
        }
    }

    /**
     * Метод, выполняемый в начале обработки.
     */
    protected function preExecute()
    {
        // тип отображения сайта
        $this->sViewMode = Design::getValidVersion($this->getStr('type', $this->sViewMode));

        // протестировать наличие необходимх файлов
        $this->testFileAccess();
    }

    /**
     * Состояние. Выбор корневого набора разделов.
     */
    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список.
     *
     * @throws \Exception
     */
    protected function actionList()
    {
        $aCssFiles = CssFiles::find()
            ->orderBy(['priority' => SORT_ASC])
            ->asArray()
            ->all();

        $this->render(new Index([
            'aCssFiles' => $aCssFiles,
        ]));
    }

    protected function actionExport()
    {
        $iId = $this->getInDataValInt('id');

        if ($iId) {
            $aData = CssFiles::find()
                ->where(['id' => $iId])
                ->asArray()
                ->one();

            $sWebFilePath = WEBPROTOCOL . WEBROOTPATH . 'files/design/out/editor_data.txt';

            $sFileName = WEBPATH . 'files/design/out/editor_data.txt';

            if (!file_exists(WEBPATH . 'files/design/out/')) {
                mkdir(WEBPATH . 'files/design/out/', 0775);
            }

            if (!is_writable(WEBPATH . 'files/')) {
                throw new \Exception("Не могу записать файл [{$sFileName}]");
            }
            $handle = fopen($sFileName, 'a');

            $bRes = file_put_contents(WEBPATH . 'files/design/out/editor_data.txt', json_encode($aData));

            fclose($handle);

            if (!$bRes) {
                throw new \Exception("Не удалось записать файл [{$sFileName}]");
            }
            $sText = "\r\n";
            $sText .= "Создан файл {$sWebFilePath}";

            $sText .= "\r\n";

            $this->render(new Export([
                'sText' => $sText,
            ]));
        }
    }

    protected function actionSort()
    {
        $aItemDrop = $this->get('data');
        $aItemTarget = $this->get('dropData');
        $sOrderType = $this->get('position');

        if ($aItemDrop and $aItemTarget and $sOrderType) {
            CssFiles::sortValues($aItemDrop, $aItemTarget, $sOrderType);
        }

        self::rebuildCssSettings();

        // перегрузить фрейм отображения
        $this->fireJSEvent('reload_display_form');
        $this->actionList();
    }

    /**
     * Детальная.
     */
    protected function actionShow()
    {
        $iId = $this->getInDataValInt('id');

        if ($iId) {
            $aData = CssFiles::find()
                ->where(['id' => $iId])
                ->asArray()
                ->one();
        } else {
            $aData = [];
        }

        $this->render(new view\Show([
            'aData' => $aData,
        ]));
    }

    protected function actionDelete()
    {
        $iId = $this->getInDataValInt('id');
        CssFiles::deleteAll(['id' => $iId]);

        $this->actionList();
    }

    /**
     * Сохранение записи.
     *
     * @throws UserException
     */
    protected function actionSave()
    {
        $aData = $this->get('data');

        $iId = $this->getInDataValInt('id');

        if (!$aData) {
            throw new UserException('Empty data');
        }
        if ($iId) {
            $oCssFile = CssFiles::findOne(['id' => $iId]);
            if (!$oCssFile) {
                throw new UserException("Запись [{$iId}] не найдена");
            }
        } else {
            $oCssFile = new CssFiles();
        }

        $aData['last_upd'] = date('Y-m-d H:i:s');

        $oCssFile->setAttributes($aData);

        $oCssFile->save(false);

        if (!$iId) {
            $oCssFile->setAttribute('priority', $oCssFile->id);
            $oCssFile->save(false);
        }

        self::rebuildCssSettings();

        // перегрузить фрейм отображения
        $this->fireJSEvent('reload_display_form');
        $this->actionList();
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        $sTitle = sprintf('%s: %s сайта', $this->name, Design::getVersionTitle($this->sViewMode));
        $oIface->setPanelTitle($sTitle);
    }

    /**
     * Обновляет набор css парметров, если они укзазаны в файлах.
     */
    public static function rebuildCssSettings()
    {
        // собрать все файлы
        $oFiles = CssFiles::find()
            ->where(['active' => '1'])
            ->orderBy(['priority' => SORT_ASC])
            ->asArray()
            ->all();

        // объединить конткнт всех файлов
        $sTmpText = '';
        foreach ($oFiles as $item) {
            $sTmpText .= $item['data'] . "\n\n";
        }

        // путь до временного файла
        $sTmpFile = FILEPATH . 'tmp_css_' . microtime(true) . '.css';

        // сохранить временный файл
        $handle = fopen($sTmpFile, 'w+');
        fwrite($handle, $sTmpText);
        fclose($handle);

        // обновить настрройки
        $oCSSParser = new CssParser();
        $oCSSParser->analyzeFile($sTmpFile);
        $oCSSParser->updateDesignSettings();

        // стареть временный файл
        unlink($sTmpFile);
    }

    /**
     * Проверяет наличие файлов и доступность их на запись.
     */
    protected function testFileAccess()
    {
        try {
            Api::testFileAccess();
        } catch (\Exception $e) {
            // объект для построения списка
            $oForm = new ext\FormView();
            $this->setInterface($oForm);

            throw $e;
        }
    }

    /**
     * Сохраняет записи из спискового интерфейса.
     */
    protected function actionSaveFromList()
    {
        $iId = $this->getInDataValInt('id');

        $sFieldName = $this->get('field_name');

        $oRow = CssFiles::findOne(['id' => $iId]);
        /** @var CssFiles $oRow */
        if (!$oRow) {
            throw new UserException("Запись [{$iId}] не найдена");
        }
        $oRow->{$sFieldName} = $this->getInDataVal($sFieldName);

        $oRow->last_upd = date('Y-m-d H:i:s');

        $oRow->save(false);

        self::rebuildCssSettings();

        // перегрузить фрейм отображения
        $this->fireJSEvent('reload_display_form');
        $this->actionInit();
    }
}
