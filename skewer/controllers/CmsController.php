<?php

namespace skewer\controllers;

use skewer\base\site_module\Request;

/**
 * Класс для основного админского слоя. также сторит интерфейс выбора файлов.
 */
class CmsController extends CmsPrototype
{
    /**
     * Основной метод админки.
     *
     * @return bool|string
     */
    public function actionAdmin()
    {
        return $this->runApplication();
    }

    public function actionNewadmin()
    {
        return $this->runApplication();
    }

    /**
     * Метод поддержания сессии в рабочем состоянии.
     */
    public function actionKeepalive()
    {
    }

    /**
     * Перекрытие инициализации языков.
     */
    protected function initLanguage()
    {
        \Yii::$app->i18n->admin->initLanguage();
    }

    /**
     * Отдает имя ключа для сессионного хранилища.
     *
     * @return string
     */
    protected function getSessionKeyName()
    {
        return 'key';
    }

    /**
     * Возвращает имя модуля основного слоя.
     *
     * @return string
     */
    public function getLayoutModuleName()
    {
        // вычисление типа вывода
        $aGlobalParams = Request::getJsonHeaders();
        $sLayoutMode = $aGlobalParams['layoutMode'] ?? '';

        // установка корневого модуля для вывода
        switch ($sLayoutMode) {
            case 'fileBrowser':
                return 'skewer\build\Cms\FileBrowser\Module';
            case 'galleryBrowser':
                return 'skewer\build\Cms\GalleryBrowser\Module';
            case 'sliderBrowser':
                return 'skewer\build\Cms\SliderBrowser\Module';
            case 'policy':
                return 'skewer\build\Cms\PolicyBrowser\Module';
            case 'tooltipBrowser':
                return 'skewer\build\Cms\TooltipBrowser\Module';
            case 'designFileBrowser':
                return 'skewer\build\Cms\FileBrowser\DesignModule';
            case 'editorMap':
                return 'skewer\build\Cms\EditorMap\Module';
            default:
                return 'skewer\build\Cms\Layout\Module';
        }
    }

    /**
     * Возвращает имя модуля авторизации.
     *
     * @return string
     */
    public function getAuthModuleName()
    {
        return 'skewer\build\Cms\Auth\Module';
    }

    /**
     * Возвращает имя первично инициализируемого модуля.
     *
     * @return string
     */
    public function getFrameModuleName()
    {
        return 'skewer\build\Cms\Frame\Module';
    }

    /**
     * Возвращает имя первично инициализируемого модуля при отсутствии авторизации.
     *
     * @return string
     */
    public function getFrameAuthModuleName()
    {
        return '';
    }

    /**
     * Отдает базовый url для сервиса.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return '/admin/';
    }
}
