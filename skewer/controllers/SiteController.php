<?php

namespace skewer\controllers;

use skewer\base\router\Router;
use skewer\base\section;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Adm\Tooltip\Module;
use skewer\build\Design\Zones\Api;
use skewer\build\Page\Main;
use skewer\build\Tool\Labels\LabelHelper;
use skewer\components\auth\Auth;
use skewer\components\auth\CurrentAdmin;
use skewer\components\auth\CurrentUser;
use skewer\components\config\Exception;
use skewer\components\config\installer;
use skewer\components\regions\RegionHelper;
use skewer\components\seo;
use skewer\helpers\Adaptive;
use skewer\helpers\Html;
use skewer\libs\Compress\ChangeAssets;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

/**
 * Контроллер лицавой части сайта.
 */
class SiteController extends Prototype
{
    /**
     * Дополнителные действия.
     *
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Отдает sys.php.
     */
    public function actionSys()
    {
        \skewer\components\redirect\Api::execute();

        $sScriptPath = RELEASEPATH . 'components/tokensAuth/sys.php';
        if (file_exists($sScriptPath)) {
            $_SERVER['SCRIPT_NAME'] = '/sys.php';
            include_once $sScriptPath;
            exit;
        }
    }

    /**
     * Лицевая страница сайта.
     *
     * @throws ServerErrorHttpException
     * @throws \Exception
     *
     * @return string
     */
    public function actionIndex()
    {
        if (self::getForceCopy()) {
            \skewer\build\Tool\Utils\Api::dropCache();
        }

        \skewer\components\redirect\Api::execute();

        if (RegionHelper::isInstallModuleRegion()) {
            RegionHelper::checkRegion();
        }

        $this->addHelpers();

        /* Определяем стартовый раздел */
        $mainSection = Auth::getMainSection();

        \Yii::$app->router->sectionId = \Yii::$app->router->getSection($mainSection);

        if (!\Yii::$app->router->sectionId) {
            \Yii::$app->router->setPage(page404);
        }

        // блок проверки на закрытие остраницы т индексации (возможно должен находиться не здесь)
        $page = TreeSection::findOne(\Yii::$app->router->sectionId);
        if ($page) {
            if ($page->visible == section\Visible::HIDDEN_NO_INDEX) {
                \Yii::$app->getResponse()->redirect('/', '301')->send();
            }
        }

        /** @var $oPage site_module\Process */
        $oPage = null;

        /* Сперва определяем язык, а затем нужные параметры */
        $this->initLang();

        $this->initParameters();

        \Yii::$app->on('reload_page_id', [$this, 'initParameters']);

        /* Редирект после языков! */
        $this->checkSiteRedirect();

        $iCnt = 0;

        do {
            // Проверяем доступ к разделу
            if (!CurrentUser::canRead(\Yii::$app->router->sectionId)) {
                \Yii::$app->router->setPage(pageAuth);
            }

            \Yii::$app->processList->removeProcess('out');

            $oPage = $this->initRootProcess();

            $iStatus = $this->executeRootProcess();

            if (++$iCnt > 10) {
                throw new ServerErrorHttpException('Infinite loop in page id determination' .
                    \Yii::$app->processList->getCurrentStateText());
            }
        } while ($iStatus == psExit);

        if (SysVar::get('site.enableLastModifiedHeader')) {
            // Дата модификации сайта
            \Yii::$app->router->setLastModifiedDate(SysVar::get('site.last_modified_date', time()));

            // Даты модификации текущего раздела, его родителей и его шаблона
            $iTemplateId = (int) Parameters::getValByName(\Yii::$app->router->sectionId, Parameters::settings, 'template');
            $aSections = Tree::getSections([$iTemplateId, \Yii::$app->router->sectionId]);
            \Yii::$app->router->setLastModifiedDate($aSections + Tree::getSections(Tree::getSectionParents(\Yii::$app->router->sectionId)));

            // Для исключенных от индексирования разделов отдаём текущую дату
            $aNoIndexedSection = seo\Data::find()->where('none_index', 1)->asArray()->get();
            if (in_array(\Yii::$app->router->sectionId, ArrayHelper::getColumn($aNoIndexedSection, 'row_id'))) {
                \Yii::$app->router->setLastModifiedDate(time());
            }

            \Yii::$app->router->sendHeaderLastModified();
        }

        $oPage->render();

        $sOutText = $oPage->getOuterText();

        $sOutText = $this->replaceShortCodes($sOutText);

        $oInstaller = new installer\Api();

        if ($oInstaller->isInstalled('TooltipBrowser', Layer::CMS) && $oInstaller->isInstalled('Tooltip', Layer::ADM)) {
            $sOutText = Module::addTooltips($sOutText);
        }

        /**
         * Сокращаем html
         * Удалим лишние пробелы.
         */
        $bCompressHtml = SysVar::get(ChangeAssets::NAMEPARAM, 1);
        if ($bCompressHtml) {
            $sOutText = Html::replaceLongSpaces($sOutText);
        }

        $sOutText = $this->parseLabels($sOutText);

        return \Yii::$app->router->modifyOut($sOutText);
    }

    /**
     * Ищет в выходной HTML вхождения и выполняет подмены.
     *
     * @param $sOutText
     *
     * @return mixed
     */
    private function replaceShortCodes($sOutText)
    {
        $aData = [
            '[site_name]' => \skewer\base\site\Site::getSiteTitle(),
            '[site_addr]' => \skewer\base\site\Site::domain(),
        ];

        $aKeys = [];
        foreach ($aData as $key => $item) {
            $aKeys[] = $key;
        }

        $sOutText = str_replace($aKeys, $aData, $sOutText);

        return $sOutText;
    }

    /**
     * Инициализировать корневой процесс
     *
     * @throws ServerErrorHttpException
     *
     * @return site_module\Process
     */
    private function initRootProcess()
    {
        $aParams = Page::getByGroup(Parameters::settings);

        $aParams = ArrayHelper::map($aParams, 'name', 'value');
        if (isset($aParams['object']) and $aParams['object']) {
            $sClassName = $aParams['object'];
        } else {
            throw new ServerErrorHttpException(sprintf(
                'No root module found for section [%d]',
                \Yii::$app->router->sectionId
            ));
        }

        $aParams['_params'] = $aParams;
        $aParams['sectionId'] = \Yii::$app->router->sectionId;

        $oProcess = \Yii::$app->processList->addProcess(new site_module\Context('out', $sClassName, ctModule, $aParams));

        if (!$oProcess) {
            throw new ServerErrorHttpException(
                sprintf('Root process [%s] not inited for section [%d]'),
                $sClassName,
                \Yii::$app->router->sectionId
            );
        }

        return $oProcess;
    }

    /**
     * Выполнить коневой процесс
     *
     * @return bool|int
     */
    private function executeRootProcess()
    {
        $iStatus = \Yii::$app->processList->executeProcessList();

        if ($this->check404()) {
            if (\Yii::$app->router->sectionId == \Yii::$app->sections->main()) {
                $sAction404 = Site::actionOnError404();

                // Устанавливаем 404 страницу и отдаем 404й код
                if ($sAction404 == Site::action_on_error404_respond_page_and_code404) {
                    \Yii::$app->router->setPage(page404);
                    $iStatus = psExit;
                // Отдаем только 404й код, при этом остаёмся на главной
                } elseif ($sAction404 == Site::action_on_error404_respond_only_code404) {
                    \Yii::$app->getResponse()->setStatusCode(404);
                }
            } else {
                \Yii::$app->router->setPage(page404);
                $iStatus = psExit;
            }
        }

        return $iStatus;
    }

    /**
     * Инициализация языков.
     */
    protected function initLanguage()
    {
        /* Перекрываем родительский метод. Инициализация будет проведена позже в initLang */
    }

    /**
     * Инициализация языков.
     */
    private function initLang()
    {
        $sLanguage = Parameters::getLanguage(\Yii::$app->router->sectionId);
        \Yii::$app->language = ($sLanguage) ?: \Yii::$app->language;
    }

    /**
     * Инициализация параметров.
     */
    public function initParameters()
    {
        // Пересобираем кэш параметров страницы
        Page::init(\Yii::$app->router->sectionId);

        // восстанавливаем дефолтное состояние страницы
        \Yii::$app->router->setStatePage(Api::DEFAULT_LAYOUT);

        // Пересобираем зоны
        $oRootProcess = \skewer\base\site\Page::getRootModule();

        if ($oRootProcess instanceof site_module\page\ModulePrototype) {
            /** @var Main\Module $oRootModule */
            $oRootModule = $oRootProcess->getModule();
            $oRootModule->initParameters();
        }
    }

    /**
     * Проверка на редиректы раздела.
     */
    private function checkSiteRedirect()
    {
        $oSection = Tree::getSection(\Yii::$app->router->sectionId);

        if (in_array(\Yii::$app->router->sectionId, [\Yii::$app->sections->getValue(Page::LANG_ROOT), \Yii::$app->sections->getValue('main')]) && \Yii::$app->router->getURLTail() != '') {
            return;
        }

        if ($oSection) {
            if ($oSection->link) {
                if (preg_match('/^\[\d+\]$/', $oSection->link)) {
                    $sRedirectUrl = \Yii::$app->router->rewriteURL($oSection->link);
                } else {
                    $sRedirectUrl = $oSection->link;
                }
                if ($sRedirectUrl !== $_SERVER['REQUEST_URI']) {
                    \Yii::$app->getResponse()->redirect($sRedirectUrl, '301')->send();
                }
            }
        }
    }

    /**
     * Добавление хелперов для парсера.
     */
    private function addHelpers()
    {
        $oAdaptive = new Adaptive();
        site_module\Parser::setParserHelper($oAdaptive, 'Adaptive');

        $oSite = new Site();
        site_module\Parser::setParserHelper($oSite, 'Site');
    }

    /**
     * Метод проверяет нужно ли выкинуть 404ю.
     *
     * @return bool true - нужно, false - нет
     */
    protected function check404()
    {
        if (\Yii::$app->router->sectionId == \Yii::$app->sections->main()) {
            // Неразобранный остаток урл
            $sTail = \Yii::$app->router->getURLTail();

            // Флаг, указывающий на непустой остаток урл
            $bTailExist = (bool) $sTail;

            if ($bTailExist) {
                // Выводимые метки
                $aActiveLabels = Main\Module::getShowLabels();

                /** @var site_module\Process $oProcess */
                foreach (\Yii::$app->processList->aProcessesPaths as $oProcess) {
                    // Пропускаем метки которые не выводятся
                    if (!in_array($oProcess->getLabel(), $aActiveLabels)) {
                        continue;
                    }

                    $oRouter = new Router($sTail, $_GET);

                    $aDecodedRules = Router::getRulesExclusionTails4MainPage($oProcess->getModuleClass(), $oProcess->getModule()->getBaseActionName());

                    $oRouter->getParams($aDecodedRules);

                    if (!$oRouter->getURLTail()) {
                        $bTailExist = false;
                        break;
                    }
                }
            }

            if ($bTailExist) {
                return true;
            }
        } else {
            // Если урл на внутренних страницах разобран не полностью
            if (!\Yii::$app->router->getUrlParsed()) {
                return true;
            }
        }

        return false;
    }

    public static function getForceCopy()
    {
        if (\Yii::$app->session->get('unsetCache') === null) {
            return \Yii::$app->assetManager->forceCopy;
        }

        return CurrentAdmin::getCacheMode();
    }

    /**
     * Парсинг меток.
     *
     * @param $subject
     *
     * @throws Exception
     *
     * @return null|string|string[]
     */
    private function parseLabels($subject)
    {
        $isInstallLabel = LabelHelper::isInstallModuleLabel();

        if (RegionHelper::isInstallModuleRegion() && $isInstallLabel) {
            $dataReplace = RegionHelper::getReplaceData();
        } elseif ($isInstallLabel) {
            $dataReplace = LabelHelper::getReplaceData();
        }

        if (isset($dataReplace)) {
            $subject = $this->replaceLabelsInMetaData($subject, $dataReplace);

            $subject = preg_replace(
                $dataReplace['pattern'],
                $dataReplace['replaces'],
                $subject
            );
        }

        return $subject;
    }

    /**
     * Замена меток без тегов в метаданных.
     *
     * @param $subject
     * @param $dataReplace
     *
     * @return mixed
     */
    private function replaceLabelsInMetaData($subject, $dataReplace)
    {
        $stringReplace = '{0}';

        $properties = [
            "\"description\" content=\"{$stringReplace}\"",
            "\"keywords\" content=\"{$stringReplace}\"",
            "\"og:title\" content=\"{$stringReplace}\"",
            "\"og:description\" content=\"{$stringReplace}\"",
            "\"og:site_name\" content=\"{$stringReplace}\"",
            '<title>' . $stringReplace . '<\/title>',
        ];

        foreach ($properties as $property) {
            $pattern = str_replace($stringReplace, '(.+?)', "/{$property}/i");

            preg_match($pattern, $subject, $match);
            //проверка наличия данных, в которых могут быть метки
            if ($match && isset($match[1])) {
                //замена меток на реальные данные метки
                $rawString = preg_replace(
                    $dataReplace['pattern'],
                    $dataReplace['replaces'],
                    $match[1]
                );

                //удаление ненужных данных их строки
                $readyString = seo\Api::prepareRawString($rawString);

                if ($rawString !== $readyString) {
                    $strReplace = stripslashes(
                        str_replace($stringReplace, $readyString, $property)
                    );

                    $subject = str_replace($match[0], $strReplace, $subject);
                }
            }
        }

        return $subject;
    }
}
