<?php

namespace skewer\build\Page\AdaptiveMode;

use skewer\base\site;
use skewer\base\site_module;
use yii\web\View;

class Module extends site_module\page\ModulePrototype
{
    /** @var string шаблон для гамбургера в шапке */
    public $headTpl = 'head_base.php';

    /** @var string шаблон для сайдбара адаптивного модуля */
    public $contentTpl = 'content_base.php';

    /** @var string отрендеренный коднент сайдбара */
    private $content = '';

    private $sAdaptiveCatalog = '';

    /** {@inheritdoc} */
    public function autoInitAsset()
    {
        // Отключаем автомат.регистрацию ассета.
        // Регистрировать будем по событию View::EVENT_END_BODY
        return false;
    }

    public function execute()
    {
        $this->setParser(parserPHP);

        /** Контент всех модулей адаптивного меню */
        $sAdaptiveMenuContent = '';

        // Дождаться выполнения всех модулей адаптивного меню и получить их контент
        if ($oPage = site\Page::getRootModule()) {
            /** @var \skewer\build\Page\Main\Module $oRootModule */
            $oRootModule = $oPage->getModule();
            $aLayouts = $oRootModule->getZones();

            if (isset($aLayouts[Api::ADP_MENU_LAYOUT_NAME]) and is_array($aLayouts[Api::ADP_MENU_LAYOUT_NAME])) {
                // Дождаться выполнения всех модулей адаптивного меню
                foreach ($aLayouts[Api::ADP_MENU_LAYOUT_NAME] as $mLabel) {
                    if (($oModule = $this->getProcess("out.{$mLabel}", psAll)) and ($oModule instanceof site_module\Process)) {
                        if (!$oModule->isCorrectCompletion()) {
                            return psWait;
                        }
                    }
                }

                // Получить контент всех модулей адаптивного меню
                foreach ($aLayouts[Api::ADP_MENU_LAYOUT_NAME] as $mLabel) {
                    $oModule = $this->getProcess("out.{$mLabel}", psAll);
                    if ($oModule instanceof site_module\Process) {
                        if ($mLabel == Api::ADP_MENU_BLOCK_CATALOG_MENU) {
                            $this->sAdaptiveCatalog = $oModule->render();
                        } else {
                            $sAdaptiveMenuContent .= $oModule->render();
                        }
                    }
                }
            }
        }

        $this->content = $sAdaptiveMenuContent;

        $this->setTemplate($this->headTpl);

        \Yii::$app->getView()->on(View::EVENT_END_BODY, [$this, 'addSidebarContent']);

        return psComplete;
    }

    /**
     * Добаялет контент сайдбара к выводу.
     */
    public function addSidebarContent()
    {
        $aOut = [
            'asset_path' => $this->getAssetWebDir(),
            'adaptive_menu_catalog' => $this->sAdaptiveCatalog,
            'adaptive_menu_content' => $this->content,
        ];

        // регистрация ассета по событию View::EVENT_END_BODY гарантирует, что файлы подключатся последними
        Asset::register(\Yii::$app->view);

        echo \Yii::$app->view->render('@skewer/build/Page/AdaptiveMode/templates/' . $this->contentTpl, $aOut);
    }
}
