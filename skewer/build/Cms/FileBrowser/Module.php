<?php

namespace skewer\build\Cms\FileBrowser;

use skewer\base\site_module\Context;
use skewer\build\Cms;
use yii\web\ServerErrorHttpException;

/**
 * Модуль для отображения раскладки файлового менеджера
 * Подчиненные модули:
 *  Дерево из основного интерфейса
 *  Панель с файлами из основного интерфейса
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        // задаем раздел по умолчанию
        $this->addInitParam('defauluSection', \Yii::$app->sections->library());

        // подключаем модули
        $this->addChildProcess(new Context('tree', 'skewer\\build\\Adm\\Tree\\FileBrowserModule', ctModule, []));
        $this->addChildProcess(new Context('files', 'skewer\\build\\Adm\\Files\\BrowserModule', ctModule, []));

        $this->setModuleLangValues([
            'fileBrowserPanelTitle' => 'fileBrowserPanelTitle',
        ]);

        $this->setCmd('init');
    }

    /** Пытается найти id раздела библиотеки по псевддониму или по имени модуля в формате [слой]_[модуль] или создать новый */
    protected function actionGetModuleNodeId()
    {
        // запрашиваем псевдоним библиотеки
        $sFolderAlias = $this->get('folder_alias');

        try {
            $iSectionId = Api::getSectionIdbyAlias($sFolderAlias);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }

        $this->setCmd('openNode');
        $this->setData('nodeId', $iSectionId);
    }
}
