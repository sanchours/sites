<?php

namespace skewer\build\Tool\Utils;

use skewer\base\orm;
use skewer\base\queue\Task;
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Tool;
use skewer\components\auth\CurrentAdmin;
use skewer\components\search\models\SearchIndex;
use skewer\components\seo\SearchTask;
use skewer\components\seo\Service;
use skewer\components\seo\Sitemap;
use skewer\components\seo\SitemapTask;

/**
 * Модуль c системными утилитами
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit($sText = '')
    {
        $this->render(new Tool\Utils\view\Init([
            'sText' => $sText,
        ]));
    }

    protected function actionDropCache()
    {
        Api::dropCache();

        $this->actionInit(\Yii::t('cache', 'drop_cache_text'));
    }

    protected function actionRebuildFavicon()
    {
        Api::rebuildFavicon();

        $this->actionInit(\Yii::t('utils', 'favicon_rebuild_success'));
    }

    protected function actionChangeCacheMode()
    {
        CurrentAdmin::changeCacheMode();
        $this->actionInit();
    }

    protected function actionLogs($sText = '')
    {
        $this->render(new Tool\Utils\view\Logs([
            'sText' => $sText,
        ]));
    }

    protected function actionViewAccess()
    {
        $sText = self::getLastLinesLogs('access');

        $this->actionLogs($sText);
    }

    protected function actionViewError()
    {
        $sText = self::getLastLinesLogs('error');

        $this->actionLogs($sText);
    }

    protected function actionClearLogs()
    {
        self::clearLogs('access');
        self::clearLogs('error');

        $this->actionLogs();
    }

    protected function actionSearch($sHeadText = '', $sText = '')
    {
        $this->render(new Tool\Utils\view\Search([
            'sHeadText' => $sHeadText,
            'sText' => $sText,
        ]));
    }

    /**
     * "Пересобрать заново" - dropAll(), затем restoreAll().
     */
    protected function actionSearchDropAll()
    {
        Service::rebuildSearchIndex();
        $sText = \Yii::t('utils', 'search_drop_index');

        $this->actionSearch($sText);
    }

    /**
     * "Сбросить автивность записей" - сбросить флаг активности для всех записей.
     */
    protected function actionResetActive()
    {
        $iAffectedRow = SearchIndex::updateAll(['status' => 0], ['status' => 1]);
        $sText = \Yii::t('utils', 'record_update') . ": {$iAffectedRow}";

        $this->actionSearch($sText);
    }

    /**
     * "Переиндексировать неактивные" - запуск переиндексации для записей со статусом 0.
     */
    protected function actionReindex()
    {
        $this->runTaskWithReboot(SearchTask::getConfig(), 'reindex', true);

        $sText = $this->buildReportReindex();

        $this->actionSearch($sText);
    }

    /**
     * Перестроим сайтмап
     */
    protected function actionRebuildSitemap()
    {
        $aTask = $this->runTaskWithReboot(SitemapTask::getConfig(), 'rebuildSitemap', true);

        $msg = $this->buildReportSitemap($aTask);

        $this->actionSearch('', $msg);
    }

    /** Оптимизация таблиц БД */
    protected function actionOptimizeDB()
    {
        orm\DB::optimizeTables();

        $this->actionInit(\Yii::t('utils', 'optimize_db_text'));
    }

    private function buildReportReindex()
    {
        $iRefreshCount = SearchIndex::find()->where(['status' => 1])->count();
        $iRefreshCountByIteration = SysVar::get('Search.updatedByIteration', 0);
        $iTotalCountError = SysVar::get('Search.countError', 0);
        $iTotalCount = SearchIndex::find()->count();
        $iProgress = ($iTotalCount > 0) ? round($iRefreshCount * 100 / $iTotalCount) : 0;

        return \Yii::$app->view->renderPhpFile(
            __DIR__ . \DIRECTORY_SEPARATOR . $this->getTplDirectory() . \DIRECTORY_SEPARATOR . 'report.php',
            [
                'iRefreshCount' => $iRefreshCount,
                'iRefreshCountByIteration' => $iRefreshCountByIteration,
                'iTotalCount' => $iTotalCount,
                'iTotalCountError' => $iTotalCountError,
                'sProgress' => $iProgress . '%',
            ]
        );
    }

    private function buildReportSitemap($aTask)
    {
        if ($aTask['status'] != Task::stError) {
            $sUrl = Site::httpDomainSlash() . Sitemap::$nameFile;

            $sPath = Sitemap::getFullFilePath();

            if (file_exists($sPath)) {
                $sText = htmlentities(file_get_contents($sPath));
                $sText = str_replace(' ', '&nbsp;', $sText);
                $sText = preg_replace('(http://.*\.xml)', '<a target="_blank" href="$0">$0</a>', $sText);
            } else {
                $sText = '';
            }

            /** @noinspection HtmlUnknownTarget */
            $msg = nl2br(sprintf(
                '%s

                <a target="_blank" href="%s">%s</a>

                %s',
                \Yii::t('utils', 'sitemap_update_msg'),
                $sUrl,
                $sUrl,
                $sText
            ));
        } else {
            $msg = \Yii::t('utils', 'sitemap_update_error');
        }

        return $msg;
    }

    /**
     * Вернёт последние $iCountLines строк логов.
     *
     * @param string $sType - тип логов
     * @param int $iCountLines - количество выводимых строк
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getLastLinesLogs($sType, $iCountLines = 2000)
    {
        if (!in_array($sType, ['error', 'access'])) {
            throw new \Exception('Не известный тип логов');
        }

        $sText = '<pre>';

        $sFile = ROOTPATH . "log/{$sType}.log";

        if (file_exists($sFile)) {
            $arr = file($sFile);

            $iStrCount = count($arr);
            if ($iStrCount) {
                $iFirstStr = ($iStrCount > $iCountLines) ? $iStrCount - $iCountLines : 0;
                for ($i = $iFirstStr; $i < $iStrCount; ++$i) {
                    $sText .= $arr[$i] . '<br>';
                }
            }
        }

        $sText .= '</pre>';

        return $sText;
    }

    /**
     * Очистит файл с логами.
     *
     * @param string $sType - тип логов
     *
     * @throws \Exception
     */
    public static function clearLogs($sType)
    {
        if (!in_array($sType, ['error', 'access'])) {
            throw new \Exception('Не известный тип логов');
        }

        $sLogFile = ROOTPATH . "/log/{$sType}.log";
        $fh = fopen($sLogFile, 'w');
        fclose($fh);
    }
}
