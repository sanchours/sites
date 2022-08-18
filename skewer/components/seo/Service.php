<?php

namespace skewer\components\seo;

use skewer\base\queue as QM;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\site\Server;
use skewer\base\site\ServicePrototype;
use skewer\base\site_module\Parser;
use skewer\build\Tool\RobotsTxt;
use skewer\components\search;
use skewer\components\search\models\SearchIndex;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Сервис для работы с СЕО компонентами.
 */
class Service extends ServicePrototype
{
    /**
     * Флаг о том что был изменен алиас при сохранении.
     *
     * @var bool
     */
    public static $bAliasChanged = false;

    /** @var array() Cлужебные слова */
    protected static $aFunctionSections = [
        '/design/',
        '/admin/',
        '/download/',
        '/cron/',
        '/contentgenerator/',
        '/gateway/',
        '/local/',
        '/assets/',
        '/files/',
        '/images/',
        '/sitemap_files/',
        '/robots_files/',
    ];

    private static $cache = [];

    /**
     * @param mixed $key
     *
     * @return string
     */
    private static function getCache($key)
    {
        return (isset(self::$cache[$key])) ? self::$cache[$key] : '';
    }

    /**
     * @param array $cache
     * @param mixed $key
     * @param mixed $value
     */
    private static function setCache($key, $value = '')
    {
        self::$cache[$key] = $value;
    }

    /**
     * Очищаем поисковый индекс
     */
    public static function rebuildSearchIndex()
    {
        SearchIndex::deleteAll();

        $aResourseList = search\Api::getResourceList();

        foreach ($aResourseList as $name => $item) {
            /** @var search\Prototype $oEngine */
            $oEngine = new $item();
            $oEngine->provideName($name);
            $oEngine->restore();
        }
    }

    /**
     * @param $sAlias
     * @param $sParentPath
     * @param $iId
     * @param $sEntity
     *
     * @return bool true - есть коллизия / false - нет коллизии
     */
    public static function checkCollision($sAlias, $sParentPath, $iId, $sEntity)
    {
        $aData = [
            'alias' => $sParentPath . $sAlias . '/',
            'class_name' => $sEntity,
        ];

        if ($iId) {
            $aData['object_id'] = $iId;
        }

        $oQuery = SearchIndex::find();

        if (isset($aData['object_id'], $aData['class_name'])) {
            $oQuery
                ->where(['!=', 'object_id', $aData['object_id']])
                ->orWhere(['!=', 'class_name', $aData['class_name']]);
        }

        $oQuery
            ->andwhere(['href' => $aData['alias']])
            ->andwhere(['has_real_url' => 1]);

        $iCount = $oQuery->count('id');

        // служебные слова
        if (isset($aData['alias']) and in_array($aData['alias'], self::$aFunctionSections)) {
            ++$iCount;
        }

        if ($iCount) {
            self::$bAliasChanged = true;

            return true;
        }

        return false;
    }

    /**
     * Формирует уникальный алиас
     *
     * @param $sAlias
     * @param $iId
     * @param $iParentId
     * @param $sEntity
     *
     * @throws UserException
     *
     * @return null|bool|string
     */
    public static function generateAlias($sAlias, $iId, $iParentId, $sEntity)
    {
        self::$bAliasChanged = false;

        if (!$sAlias) {
            $sAlias = date('d-m-Y-H-i');
        }

        // Счетчик итераций
        $iIteration = 1;

        // Мин.длина alias
        $iMinLengthAlias = 1;

        // Максимальная длина ссылки(alias_section + alias_entity)
        $iMaxLengthFullAlias = SearchIndex::getTableSchema()->getColumn('href')->size;

        // Флаг, указывающий что к alias'у ранее добавлялась цифра
        $bIsAddNumber = false;

        if (!isset(self::$cache[$iParentId])) {
            $sParentPath = Tree::getSectionAliasPath($iParentId);
            self::setCache($iParentId, $sParentPath);
        }
        $sParentPath = self::getCache($iParentId);

        $iTmpLengthFullPath = mb_strlen($sParentPath . $sAlias . '/');

        if ($iTmpLengthFullPath > $iMaxLengthFullAlias) {
            if (mb_strlen($sAlias) - ($iTmpLengthFullPath - $iMaxLengthFullAlias) < $iMinLengthAlias) {
                throw new UserException(\Yii::t('tree', 'error_can_not_create_alias'));
            }
            $sAlias = mb_substr($sAlias, 0, mb_strlen($sAlias) - ($iTmpLengthFullPath - $iMaxLengthFullAlias));
        }

        // Работаем пока есть коллизия
        while (self::checkCollision($sAlias, $sParentPath, $iId, $sEntity)) {
            if ($iIteration >= 100) {
                throw new UserException(\Yii::t('tree', 'error_can_not_create_alias'));
            }
            self::$bAliasChanged = true;

            $iLengthAlias = mb_strlen($sAlias);

            $iLengthFullPath = mb_strlen($sParentPath . $sAlias . '/');

            if ($bIsAddNumber) {
                $iPrevIteration = $iIteration - 1;
                $sTmpTail = "-{$iPrevIteration}";
                $sAlias = mb_substr($sAlias, 0, mb_strlen($sAlias) - mb_strlen($sTmpTail));
            }

            if ($iLengthFullPath == $iMaxLengthFullAlias || ($iLengthFullPath == ($iMaxLengthFullAlias - 1))) {
                if (!$bIsAddNumber) {
                    // Укорачиваем alias на 1 символ
                    if ($iLengthAlias <= $iMinLengthAlias) {
                        throw new UserException(\Yii::t('tree', 'error_can_not_create_alias'));
                    }
                    $sAlias = mb_substr($sAlias, 0, $iLengthAlias - 1);
                } else {
                    // Если $iIteration увеличило количество разрядов (10->100, 100->1000)
                    if (mb_strlen((string) $iIteration) > mb_strlen((string) ($iIteration - 1))) {
                        if (mb_strlen($sAlias) <= $iMinLengthAlias) {
                            throw new UserException(\Yii::t('tree', 'error_can_not_create_alias'));
                        }
                        $sAlias = mb_substr($sAlias, 0, mb_strlen($sAlias) - 1);
                    }

                    // Добавляем в конец alias '-цифра'
                    $sAlias .= '-' . $iIteration;
                    ++$iIteration;
                    $bIsAddNumber = true;
                }
            } else {
                // Добавляем в конец alias '-цифра' */
                $sAlias .= '-' . $iIteration;
                ++$iIteration;
                $bIsAddNumber = true;
            }
        }

        return $sAlias;
    }

    /**
     * Обновляет поисковый индекс
     *
     * @param $iTask
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function makeSearchIndex($iTask = 0)
    {
        return QM\Task::runTask(SearchTask::getConfig(), $iTask, true);
    }

    /**
     * Постановка задачи на обновление search index.
     *
     * @static
     *
     * @return bool
     */
    public static function updateSearchIndex()
    {
        return QM\Api::addTask(SearchTask::getConfig());
    }

    /**
     * Постановка задачи на обновление sitemap.xml.
     *
     * @static
     *
     * @return bool
     */
    public static function updateSiteMap()
    {
        return QM\Api::addTask(SitemapTask::getConfig());
    }

    /**
     * Обновляет карту сайта.
     *
     * @param $iTask
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function makeSiteMap($iTask = false)
    {
        return QM\Task::runTask(SitemapTask::getConfig(), $iTask, true);
    }

    public static function setNewDomainToSiteMap()
    {
        self::updateSiteMap();

        return true;
    }

    public static function updateRobotsTxt($sDomain)
    {
        $out = self::getContentRobotsTxtFile($sDomain);

        // -- save - rewrite file

        $filename = Robots::getFullFilePath();

        if (file_exists($filename) && !is_writable($filename)) {
            throw new \Exception('Can\'t write robots.txt');
        }
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
     * Получить содержимое robots.txt
     * (!!!контент, записываемый в файл при его перестроении, но не контент считанный из файла).
     *
     * @param string - домен
     * @param mixed $sDomain
     *
     * @return string
     */
    public static function getContentRobotsTxtFile($sDomain)
    {
        $aConfigPaths = \Yii::$app->getParam(['parser', 'default', 'paths']);
        if (is_array($aConfigPaths) && isset($aConfigPaths[0])) {
            $aConfigPaths = $aConfigPaths[0];
        }

        if ((RobotsTxt\Api::getSysVar('content_overridden', '0') === '1')) {
            $sRobotsRules = RobotsTxt\Api::getSysVar('robots_content', '');
            $sRobotsRules = $sRobotsRules . "\r\n" . Parser::parseTwig('robots_host.twig', [
                'site_url' => WEBPROTOCOL . $sDomain,
            ], $aConfigPaths);

            $sRobotsContent = $sRobotsRules;
        } else {
            $sRobotsContent = self::generateDefaultContentRobotsTxtFile($sDomain);
        }

        return $sRobotsContent;
    }

    /**
     * Пути системных разделов.
     *
     * @return array
     */
    private static function getSystemPaths()
    {
        $aPaths = [];
        $aServices = [];
        foreach (['search', 'card', 'auth', 'profile'] as $key) {
            $aServices[$key] = \Yii::$app->sections->getValues($key);
        }

        if ($aServices) {
            $aPaths = TreeSection::find()
                ->where(['id' => $aServices])
                ->asArray()
                ->all();
        }

        return ArrayHelper::map($aPaths, 'alias_path', 'alias_path');
    }

    /**
     * Сгенирирует содержимое (по умолчанию) файла robots.txt.
     *
     * @param string $sDomain  - домен сайта
     * @param bool $bOnlyRules - вернуть только правила (disallow/allow)
     *
     * @return string
     */
    public static function generateDefaultContentRobotsTxtFile($sDomain, $bOnlyRules = false)
    {
        // набор предустановленных в конфиге путей для парсинга
        $aConfigPaths = \Yii::$app->getParam(['parser', 'default', 'paths']);
        if (is_array($aConfigPaths) && isset($aConfigPaths[0])) {
            $aConfigPaths = $aConfigPaths[0];
        }

        $bExistDomain = false;

        if ($sDomain) {
            $bExistDomain = true;
        }

        if (!Server::isProduction()) {
            $bExistDomain = false;
        }

        $aData = [
            'domain_exist' => $bExistDomain,
            'site_url' => WEBPROTOCOL . $sDomain,
            'pattern' => Api::getRobotsPattern(),
            'system_service' => self::getSystemPaths(),
            'bOnlyRules' => $bOnlyRules,
        ];

        $sOut = Parser::parseTwig('robots.twig', $aData, $aConfigPaths);

        return $sOut;
    }
}
