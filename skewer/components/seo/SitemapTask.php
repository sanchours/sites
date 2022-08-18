<?php

namespace skewer\components\seo;

use skewer\base\queue\Task;
use skewer\base\site\Site;
use skewer\base\Twig;
use skewer\components\auth\Auth;
use skewer\components\search;
use skewer\components\search\models\SearchIndex;

/**
 * Задача на обновление карты сайта.
 */
class SitemapTask extends Task
{
    private $sTemplateDir = '';

    /** @var int номер файла */
    private $file_num = 0;

    private $sMainDomain = '';

    const LIMIT = 5000;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $dir = WEBPATH . 'sitemap_files/';

        if (!is_dir($dir)) {
            @mkdir($dir);
        }

        $this->clearSubFiles();
    }

    /**
     * Удаляет все файлы второго уровня.
     */
    private function clearSubFiles()
    {
        $dir = WEBPATH . 'sitemap_files/';

        /* чистка старых сайтмапов */
        if (file_exists($dir)) {
            $aList = glob($dir . '/*');
            if (is_array($aList)) {
                foreach ($aList as $file) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function recovery()
    {
        $argc = func_get_args();

        $this->file_num = (isset($argc[0]['file_num'])) ? $argc[0]['file_num'] : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $this->sTemplateDir = __DIR__ . '/templates/';

        $this->sMainDomain = Site::httpDomain();

        $aConfigPaths = \Yii::$app->getParam(['parser', 'default', 'paths']);
        if (!is_array($aConfigPaths)) {
            $aConfigPaths = [];
        }
        $aConfigPaths[] = $this->sTemplateDir;
        Twig::setPath($aConfigPaths);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $dir = SITEMAP_FILEPATH;

        // взять записи для заданного номера файла
        $aItems = $this->getPageList($this->file_num);

        // если ничего не выбрано - закончить
        if (count($aItems) == 0) {
            $this->setStatus(static::stComplete);

            return true;
        }

        // -- parse

        Twig::assign('items', $aItems);

        $out = Twig::render('sitemap.twig');

        // -- save - rewrite file

        $title = sprintf('sitemap.%d.xml', $this->file_num + 1);

        $filename = $dir . $title;

        if (!$handle = fopen($filename, 'w+')) {
            return false;
        }

        if (fwrite($handle, $out) === false) {
            return false;
        }

        fclose($handle);

        // увеличение счетчика файлов
        ++$this->file_num;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterExecute()
    {
        // забацаем главный сайтмап
        $aFiles = [];
        $oDateTime = new \DateTime();
        $currentTime = $oDateTime->format(\DateTime::W3C);

        $aFilesOnDisk = glob(SITEMAP_FILEPATH . '*');

        // если у нас только один файл
        if (count($aFilesOnDisk) === 1) {
            // будем использовать его контент как корневой файл
            $out = file_get_contents($aFilesOnDisk[0]);

            // а сам файл сотрем
            $this->clearSubFiles();
        } else {
            // если файлов несколько - собираем файл со списком
            foreach ($aFilesOnDisk as $file) {
                $aFiles[] = [
                    'url' => $this->sMainDomain . '/sitemap_files/' . basename($file),
                    'modify_date' => $currentTime,
                ];
            }
            Twig::assign('files', $aFiles);
            $out = Twig::render('main_sitemap.twig');
        }

        $filename = Sitemap::getFullFilePath();

        if (!$handle = fopen($filename, 'w+')) {
            return false;
        }
        if (fwrite($handle, $out) === false) {
            return false;
        }
        fclose($handle);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function reservation()
    {
        $this->setParams(['file_num' => $this->file_num]);
    }

    /**
     * Отдает набор записей для составления карты сайта.
     *
     * @param int $file_num
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getPageList($file_num = 0)
    {
        $oQuery = SearchIndex::find()->where(['status' => 1, 'use_in_sitemap' => 1]);

        if ($aDenySections = Auth::getDenySectionByUserId()) {
            $oQuery->andWhere(['not in', 'section_id', $aDenySections]);
        }

        $oQuery
            ->offset($file_num * self::LIMIT)
            ->limit(self::LIMIT)
            ->orderBy(['modify_date' => SORT_DESC]);

        $aPages = [];

        /** @var search\models\SearchIndex $aRows */
        $aRows = $oQuery->all();

        foreach ($aRows as $aRow) {
            $oDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $aRow['modify_date']);

            $aPages[] = [
                'modify_date' => $oDateTime->format(\DateTime::W3C),
                'url' => $this->sMainDomain . $aRow['href'],
                'priority' => $aRow['priority'],
                'frequency' => $aRow['frequency'],
            ];
        }

        return $aPages;
    }

    /**
     * Получить имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Получить конфиг задачи.
     *
     * @return array
     */
    public static function getConfig()
    {
        return [
            'title' => 'sitemap update',
            'class' => self::className(),
            'priority' => self::priorityHigh,
        ];
    }
}
