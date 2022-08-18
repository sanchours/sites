<?php

namespace skewer\build\Tool\HttpsMode;

use skewer\base\SysVar;
use skewer\build\Tool;

/**
 * Модуль переключения сайта в работу на протоке https.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    const MESSAGE_DELAY = 3000;

    /**
     * Вызывается в случае отсутствия явного обработчика.
     */
    protected function actionInit()
    {
        // включение редиректа на https
        $this->render(new Tool\HttpsMode\view\Index([
            'bActiveHTTPS' => (bool) SysVar::get('enableHTTPS'),
        ]));
    }

    /** отключение редиректа на https для sitemap`а */
    protected function actionStopHttps()
    {
        SysVar::set('enableHTTPS', 0);
        $this->updateExportFiles();

        $this->actionInit();
    }

    /** Редирект на https для sitemap`а */
    protected function actionStartHttps()
    {
        SysVar::set('enableHTTPS', 1);
        $this->updateExportFiles();

        $this->actionInit();
    }

    /** Обновить файлы, содержащие ссылки на ресурсы сайта */
    private function updateExportFiles()
    {
        $this->addMessage('<b>Необходимо обновить страницу, потом перестроить SITEMAP и robots.txt </b>', '', self::MESSAGE_DELAY);
    }
}
