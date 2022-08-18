<?php

namespace skewer\base\site;

use skewer\base\site_module;
use skewer\build\Page\Text;

/**
 * Класс для работы с текущей открытой страницей
 * Предоставляет единый интерфейс для работы с модулями на странице.
 */
class Page
{
    /**
     * Задает новый заголовок для страницы
     * Для скрытия заголовка можно передать false.
     *
     * @param false|string $sTitle новый заголовок
     *
     * @return bool если был заменен, то true
     */
    public static function setTitle($sTitle)
    {
        // находим модуль вывода заголовка
        $oProcessTitle = \Yii::$app->processList->getProcess('out.title', psAll);

        // если он на странице есть
        if ($oProcessTitle instanceof site_module\Process) {
            // задаем переменную с названием
            \Yii::$app->environment->set('title4section', $sTitle);

            // ставим статус на перезагрузку, если он уже отработал
            $oProcessTitle->setStatus(psNew);

            return true;
        }

        return false;
    }

    /**
     * Добавляем элемент в "хлебные крошки".
     * Но только один. Повторный вызов функции вызовет перекрытие предыдущего значения.
     *
     * @param string $sTitle текст элемента
     * @param string $sHref ссылка (если нужна)
     * @param bool $bSelected - флаг, указывающий на то, что мы находимся на этой странице
     *
     * @return bool
     */
    public static function setAddPathItem($sTitle, $sHref = '', $bSelected = true)
    {
        return self::setAddPathItemData([
            'title' => $sTitle,
            'link' => $sHref,
            'selected' => $bSelected,
        ]);
    }

    /**
     * Добавляем элемент в "хлебные крошки".
     * Но только один. Повторный вызов функции вызовет перекрытие предыдущего значения
     * Принимает массив. Функция добавлена на случай, если потребуется собирать
     * сложный элемент. Для большинства случаев подойдет setAddPathItem.
     *
     * @param [] $aData данные для добавления
     *
     * @return bool
     */
    public static function setAddPathItemData($aData)
    {
        $oProcessPathLine = \Yii::$app->processList->getProcess('out.pathLine', psAll);

        if ($oProcessPathLine instanceof site_module\Process) {
            $oProcessPathLine->setStatus(psNew);

            \Yii::$app->environment->add(
                'pathline_additem',
                [
                    'id' => $aData['id'] ?? 0,
                    'title' => $aData['title'] ?? 'title',
                    'alias_path' => $aData['alias'] ?? '',
                    'href' => $aData['link'] ?? '',
                    'selected' => !empty($aData['selected']),
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * Отдает true если главный модуль уже отработал.
     *
     * @return bool
     */
    public static function rootModuleComplete()
    {
        $oPage = self::getRootModule();

        return $oPage->isComplete();
    }

    /**
     * Отдает корневой модуль на страницу (обычно это Page\Main).
     *
     * @return site_module\Process
     */
    public static function getRootModule()
    {
        return \Yii::$app->processList->getProcess('out', psAll);
    }

    /**
     * Отдает главный модуль на страницу (каталог / новостная / ...).
     *
     * @return null|site_module\Process
     */
    public static function getMainModuleProcess()
    {
        $oProcess = \Yii::$app->processList->getProcess('out.content', psAll);

        return ($oProcess instanceof site_module\Process) ? $oProcess : null;
    }

    /**
     * Отдает true если модуль отработал
     * Если модуль не существует в указанной метке - вернется true.
     *
     * @param $sPath - полный путь от корневого процесса
     *
     * @return bool|int
     */
    public static function isCompleteModule($sPath)
    {
        $oProcess = \Yii::$app->processList->getProcess($sPath, psAll);

        return ($oProcess instanceof site_module\Process) ? $oProcess->isComplete() : true;
    }

    /**
     * Очищает данные процесса.
     *
     * @param $sPath - полный путь от корневого процесса
     */
    private static function clearProcessData($sPath)
    {
        $oProcess = \Yii::$app->processList->getProcess($sPath);
        if ($oProcess instanceof site_module\Process) {
            $oProcess->clearData();
        }
    }

    /**
     * Заставляет модуль SEO перезагрузить содержание
     * Вызовом этого метода помечены все места перестроения SEO.
     *
     * @return bool
     */
    public static function reloadSEO()
    {
        $page = \Yii::$app->processList->getProcess('out.SEOMetatags', psAll);
        if ($page instanceof site_module\Process) {
            $page->setStatus(psNew);

            return true;
        }

        return false;
    }

    /**
     * Перекрывает staticContent.
     *
     * @param string $sContent - текст
     *
     * @return bool
     */
    public static function setStaticContent($sContent)
    {
        $sLabel = 'staticContent';

        $oStaticContentProcess = \Yii::$app->processList->getProcess("out.{$sLabel}", psAll);

        if ($oStaticContentProcess instanceof site_module\Process) {
            $oStaticContentProcess->setStatus(psNew);

            $aParams = \skewer\base\section\Page::getByGroup($sLabel);
            if (isset($aParams['source'])) {
                $aParams['source']['show_val'] = $sContent;
            }

            \Yii::$app->environment->add('moduleParams', [
                'nameModule' => Text\Module::getNameModule(),
                'label' => $sLabel,
                'data' => $aParams,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Перекрывает метатэги в обход seo-шаблонам
     *
     * @param array $aMetaTags - массив метатэгов
     *
     * @return bool
     */
    public static function setMetaTags($aMetaTags)
    {
        $oProcessTitle = \Yii::$app->processList->getProcess('out.SEOMetatags', psAll);

        if ($oProcessTitle instanceof site_module\Process) {
            foreach ($aMetaTags as $sTagName => $sTagValue) {
                \Yii::$app->environment->set($sTagName, $sTagValue);
            }

            $oProcessTitle->setStatus(psNew);

            return true;
        }

        return false;
    }
}
