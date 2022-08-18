<?php

namespace skewer\build\Page\Main;

use Codeception\Module\Yii2;
use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Design\Zones;
use skewer\build\Page\Main\Seo as SeoData;
use skewer\build\Page\Subscribe;
use skewer\build\Tool\UnderConstruction\Api as ApiUnderConst;
use skewer\components;
use skewer\components\content_generator as content_generator;
use skewer\components\design\Design;
use skewer\components\design\DesignManager;
use skewer\components\design\model\Groups;
use skewer\helpers\BrowserClass;
use skewer\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\ServerErrorHttpException;
use yii\web\View;

/**
 * Модуль построения раздела публичной части сайта.
 */
class Module extends site_module\page\ModulePrototype
{
    /**
     * Id текущего раздела.
     *
     * @var int
     */
    public $sectionId = 0;

    /** @var string Шаблон вывода */
    public $templateFile = '';

    /**
     * Зоны.
     *
     * @var array
     */
    protected $aZonesByGroup = [];

    /**
     * параметр если установлен - раздел скрывется из поиска и sitemap
     * Называеться должен как значение skewer\base\section\Tree::removeFromSearchParam.
     *
     * @var mixed
     */
    public $removeFromSearch = false;

    /**
     * Массив зон с метками.
     *
     * @var array
     */
    protected $aZones = [];

    /** @var string шаблон заглушки */
    public $sBlockTemplate = 'showBlock.php';

    public function init()
    {
        $this->setParser(parserPHP);

        if (!$this->templateFile) {
            throw new ServerErrorHttpException('Root process template is not set');
        }
        $this->initParameters();
        \Yii::info($this);
    }

    // func

    /**
     * Исполнение модуля.
     *
     * @return int
     */
    public function execute()
    {
        //дописать
        if (ApiUnderConst::isShowBlock()) {
            $this->setData('showBlock', ApiUnderConst::getDataBlock());
            $this->setTemplate($this->sBlockTemplate);

            return psComplete;
        }

        $this->sectionId = $this->getInt('sectionId', $this->sectionId);

        /*
         * #fix404
         * на 404 может перекинуть после отработки нескольких модулей, надо сбросить уже установленные параметры окружения
         */
        if ($this->sectionId == \Yii::$app->sections->page404()) {
            \Yii::$app->environment->clear();
            // и не учитывать неразобранный хвост URL
            \Yii::$app->router->setUriParsed();
        }

        $this->setEnvParam('sectionId', $this->sectionId);

        $this->setData('sectionId', $this->sectionId);

        $this->setData('iMinWidthForForm', SysVar::get('Page.min_site_width_for_form'));

        $this->setData('favicon', SysVar::get('favicon.html', ''));

        $this->setData('adaptive_parameters', self::getAdaptiveParameters());

        $this->setData('mainId', \Yii::$app->sections->main());

        if (Design::modeIsActive()) {
            $this->setData('designMode', Design::getDirList());
        }

        foreach (Page::getByGroup(Parameters::settings) as $aParamsSet) {
            $this->setData($aParamsSet['name'], ['value' => $aParamsSet['value'], 'text' => $aParamsSet['show_val']]);
        }

        $this->setProcess();

        content_generator\Asset::setSectionId($this->sectionId);

        $this->setSEO(new SeoData($this->sectionId(), $this->sectionId()));

        $this->setData('SiteName', Site::domain());

        $this->setData('canonical_url', false);

        $this->setBodyClass();

        $this->setFooterData();

        $this->setTemplate($this->templateFile);

        return psComplete;
    }

    /**
     * Инициализация параметров класса.
     */
    protected function initParameters()
    {
        $this->aZones = $this->aZonesByGroup = [];
        foreach (Page::getByGroup(Zones\Api::layoutGroupName) as $sZone => $sZoneVal) {
            $aZoneSource = StringHelper::explode($sZoneVal['show_val'], ',', true, true);

            $this->aZones[$sZone] = [];

            foreach ($aZoneSource as $label) {
                $this->aZones[$sZone][] = trim($label);

                // пропуск служебных значений, начинающихся с "."
                if (mb_strpos($sZone, '.') === false) {
                    $this->aZonesByGroup[trim($label)] = $sZone;
                }
            }
        } // foreach
    }

    /**
     * Формирование списка процессов
     * В список будут включены модули:
     *  * активированные в дизайнерском режиме
     *  * не содержащие параметра layout (системные)
     *  * имеющие флаг force_include.
     */
    protected function setProcess()
    {
        $aGroups = Page::getGroups();

        if (in_array('AdaptiveMode', $aGroups)) {
            $iPos = array_search('AdaptiveMode', $aGroups);
            $aGroups[$iPos] = '';
            $aGroups[] = 'AdaptiveMode';
        }

        foreach ($aGroups as $sGroupName) {
            if ($sGroupName == Parameters::settings) {
                continue;
            }

            if (Page::getVal($sGroupName, Parameters::object)) {
                // флаг добавления процесса в список на обработку
                $bAdd = false;

                $aParams = Page::getByGroup($sGroupName);
                $aParams = ArrayHelper::map($aParams, 'name', 'value');

                // добавление зоны, если есть
                if (isset($this->aZonesByGroup[$sGroupName])) {
                    if (($iPosSeparate = mb_strpos($this->aZonesByGroup[$sGroupName], ':')) !== false) {
                        $sZone = mb_substr($this->aZonesByGroup[$sGroupName], 0, $iPosSeparate);
                    } else {
                        $sZone = $this->aZonesByGroup[$sGroupName];
                    }

                    $aParams['zone'] = $sZone;
                    $bAdd = true;
                }

                // если нет метки зоны вывода, то модуль системный - добавляем принудительно
                if (!isset($aParams['layout'])) {
                    $bAdd = true;
                }

                // флаг принудаительно включения модйля в список на обработку
                if (isset($aParams['force_include']) and $aParams['force_include']) {
                    $bAdd = true;
                }

                // Добавление процесса, если есть флаг
                if ($bAdd) {
                    $this->addProcess($sGroupName, $aParams);
                }
            }
        }// each
    }

    public function setSEO(components\seo\SeoPrototype $oSeo)
    {
        $this->setEnvParam(components\seo\Api::SEO_COMPONENT, $oSeo);

        $this->setEnvParam(components\seo\Api::OPENGRAPH, $this->renderTemplate('OpenGraph.php', [
            'oTree' => Tree::getSection($this->sectionId()),
            'oSeoComponent' => $oSeo,
        ]));
    }

    /** {@inheritdoc} */
    public function beforeRender()
    {
        \Yii::$app->getView()->on(View::EVENT_END_BODY, static function () {
            // дополнительный css файл из диз режима
            // вызывается в самом конце, чтобы гарантированно был последним
            AddCssAsset::register(\Yii::$app->view);
        });

        $this->setLayouts();

        if (!SysVar::get('lock_section_flag', false)) {
            return;
        }

        $bHasContent = false;

        // перебираем все инициализированные процессы в дереве
        foreach (\Yii::$app->processList->aProcessesPaths as $oProcess) {
            // берем активные модули ...
            $oModule = $oProcess->getModule();
            if (!$oModule) {
                continue;
            }

            // ... наследники прототипа клиентского модуля ...
            if (!$oModule instanceof site_module\page\ModulePrototype) {
                continue;
            }

            // ... которые могут иметь контент ...
            if (!$oModule->canHaveContent()) {
                continue;
            }

            // для отладки раскомментировать следующую строку
            // var_dump( $oModule->getModuleName().' - '.(Html::hasContent($oModule->oContext->getOuterText()) ? 'HAS CONTENT' : ''), $oModule->oContext->getOuterText() );

            if (Html::hasContent($oModule->oContext->getOuterText())) {
                $bHasContent = true;
            }
        }

        if (!$bHasContent) {
            $Layout = $this->getData(Zones\Api::layoutGroupName);
            $aContentZone = ArrayHelper::getValue($Layout, 'content');
            if ($aContentZone) {
                // добавить в зону вывода
                $aContentZone[] = 'devSection';
                $Layout['content'] = $aContentZone;
                $this->setData(Zones\Api::layoutGroupName, $Layout);
                $this->setData('devSection', \Yii::t('page', 'lock_section_text_value'));
            }
        }
    }

    /**
     * Метод выполняет установку в шаблон layout-oв и их меток.
     */
    protected function setLayouts()
    {
        $aZonesToLabels = self::getShowLabels(true);

        // Удаляем зоны без меток
        $aZonesToLabels = array_filter($aZonesToLabels, static function ($item) {
            return ($item) ? true : false;
        });

        // Устанавливаем layouts
        $this->setData(Zones\Api::layoutGroupName, $aZonesToLabels);
    }

    /**
     * Получить выводимые на странице метки.
     *
     * @param bool $bGroup - группировать метки по зонам ?
     *
     * @return array| bool  массив меток или false в случае если out-процесс еще не отработал
     */
    public static function getShowLabels($bGroup = false)
    {
        $oRootProcess = \skewer\base\site\Page::getRootModule();

        if (!$oRootProcess->isComplete()) {
            return false;
        }

        /** @var Module $oRootModule */
        $oRootModule = $oRootProcess->getModule();

        $aZonesList = array_keys($oRootModule->aZones);

        /** @var array Зоны сгруппированные по состоянию */
        $aZonesByState = [];
        foreach ($aZonesList as $sZoneName) {
            $sState = (($iPos = mb_strpos($sZoneName, ':')) !== false)
                ? mb_substr($sZoneName, $iPos + 1)
                : Zones\Api::DEFAULT_LAYOUT;

            $aZonesByState[$sState][$sZoneName] = $sZoneName;
        }

        /** @var string Текущее состояние страницы */
        $sCurrentStatePage = \Yii::$app->router->getStatePage();

        /** @var array Зоны текущего состояния страницы */
        $aZonesActiveState = ArrayHelper::getValue($aZonesByState, $sCurrentStatePage, []);

        /** @var array Выводимые зоны */
        $aZones4Show = $aZonesActiveState;

        $fGetZoneName = static function ($sZoneAndState) {
            return (($iPos = mb_strpos($sZoneAndState, ':')) !== false)
                ? mb_substr($sZoneAndState, 0, $iPos)
                : $sZoneAndState;
        };

        if ($sCurrentStatePage !== Zones\Api::DEFAULT_LAYOUT) {
            $aZonesActiveStateWithoutPreffix = array_map($fGetZoneName, $aZonesActiveState);
            $aDefaultStateZones = ArrayHelper::getValue($aZonesByState, Zones\Api::DEFAULT_LAYOUT, []);

            foreach ($aDefaultStateZones as $item) {
                if (!in_array($item, $aZonesActiveStateWithoutPreffix)) {
                    $aZones4Show[$item] = $item;
                }
            }
        }

        $aZones4ShowToLabel = array_intersect_key($oRootModule->aZones, $aZones4Show);

        $aOutZones = [];

        foreach ($aZones4ShowToLabel as $key => $value) {
            if ($bGroup) {
                $aOutZones[$fGetZoneName($key)] = $value;
            } else {
                $aOutZones = array_merge($aOutZones, $value);
            }
        }

        return $aOutZones;
    }

    /**
     * Вернет зоны(layouts) страницы с метками.
     *
     * @return array
     */
    public function getZones()
    {
        return $this->aZones;
    }

    /**
     * Параметры адаптивного режима.
     */
    private static function getAdaptiveParameters()
    {
        $oAdaptiveGroup = Groups::findOne(['name' => 'adaptive', 'layer' => 'default']);

        if (!$oAdaptiveGroup) {
            return '';
        }

        $aAdaptiveParams = DesignManager::getParamsByGroup($oAdaptiveGroup->id);
        $aAdaptiveParams = ArrayHelper::map(
            $aAdaptiveParams,
            static function ($item) {
                return str_replace('adaptive.', '', $item['name']);
            },
            'value'
        );

        $sJson = Json::encode($aAdaptiveParams);

        return $sJson;
    }

    /**
     * Установка данных в шаблон футера
     * Поскольку на данный момент для футера нет заведенной через параметр зоны,
     * вызов модулей и вывод необходимых данных в шаблон производим здесь.
     */
    public function setFooterData()
    {
        if (Page::getShowVal('.layout', 'footer_tpl') == 'foot_video') {
            if (\Yii::$app->register->moduleExists(Subscribe\Module::getNameModule(), Layer::PAGE)) {
                $sHtml = $this->createAndExecuteProcess('SubscribeFooter', Subscribe\Module::className(), [
                    'sMiniTpl' => 'miniFormSimpleInput.twig',
                ]);

                $this->setData('SubscribeFooter', $sHtml);
            }
        }
    }

    /**
     * Установить класс html-тега body.
     */
    public function setBodyClass()
    {
        $class = (Page::getVal('.', 'page_class')) ?: '';

        $browserClass = BrowserClass::get();

        $class = $class ? "{$class} {$browserClass}" : $browserClass;

        $this->setData('sBodyClass', $class);
    }

    /**
     * @param $sGroupName
     * @param array $aParams
     * @throws ServerErrorHttpException
     */
    protected function addProcess($sGroupName, array $aParams)
    {
        $className = site_module\Module::getClass(Page::getVal($sGroupName, Parameters::object), Layer::PAGE);
        $process = $this->addChildProcess(new site_module\Context($sGroupName, $className, ctModule, $aParams));
        if (!empty($aParams['html_class'])) {
            $process->setData('html_class', $aParams['html_class']);
        }
    }
}
