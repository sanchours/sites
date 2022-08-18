<?php

namespace skewer\build\Page\Search;

use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Page\CatalogViewer;
use skewer\components\catalog\Card;
use skewer\components\forms\FormBuilder;
use skewer\components\search\Api;
use skewer\components\search\Type;

/**
 * Модуль контекстного поиска по сайту
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    public $onPage = 10;

    /** @var string Тип шаблона мини-формы */
    public $miniFormTemplate = 'mini_form.twig';

    /** @var string Тип шаблона для поиска по каталогу */
    public $sCatalogListTemplate = 'list';

    /** @var int Тип поиска по области */
    public $search_type = 0;

    /** @var int Тип поиска */
    public $type = 1;

    /** @var int Раздел */
    public $search_section = 0;

    /** @var bool Поиск по подразделам */
    public $bSubsection = true;

    /** @var int Форма заказа товаров */
    public $form_section = 0;

    /**
     * Максимальна длина текста, оображаемого в результатах поиска.
     *
     * @var int
     */
    private $iLength = 500;

    /** @var string шаблон общего результата поиска */
    public $sAllTemplate = 'results.twig';

    /** @var string шаблон каталожного результата поиска */
    public $sCatalogTemplate = 'CatalogResults.twig';

    public function init()
    {
        $this->type = $this->getInt('search_type', -1);
        if ($this->type < 0) {
            $this->type = (int) SysVar::get('Search.default_type');
        }
    }

    public function execute()
    {
        if ($this->sectionId() == \Yii::$app->sections->getValue('search')) {
            $this->showForm();

            switch ($this->search_type) {
                case Type::inAll:
                case Type::inInfo:
                default:
                    $this->setTemplate($this->sAllTemplate);
                    $this->getInfo();
                    break;

                case Type::inCatalog:
                    // при переходе на php шаблоны это должно быть перенесено в них
                    CatalogViewer\Asset::register(\Yii::$app->view);
                    $this->setTemplate($this->sCatalogTemplate);
                    $this->getCatalog();
                    break;
            }

            return psComplete;
        }
        // mini bar
        $this->setTemplate($this->miniFormTemplate);
        $this->setData(
            'bHidePlaceHolder',
            SysVar::get('Search.hidePlaceHolder', false)
            );
        $this->setData(
            'search_section',
            \Yii::$app->sections->getValue('search')
            );
        $this->setData('label', $this->getLabel());

        return psComplete;
    }

    /**
     * Вывод формы поиска.
     *
     * @throws \Exception
     */
    private function showForm()
    {
        $sectionId = $this->getEnvParam('sectionId');
        $searchEntity = new SearchEntity($sectionId, $_GET);

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $searchEntity,
            $sectionId,
            $label
        );

        $this->search_section = $this->search_section ?: $this->getStr('search_section');

        $this->setData('form', $formBuilder->getFormTemplate());
    }

    /**
     * Получение данных для каталожного поиска.
     */
    private function getCatalog()
    {
        $iPage = $this->getInt('page', 1);
        $sSearchText = $this->getStr('search_text');
        $iOnPage = (int) $this->onPage;

        if (!empty($sSearchText)) {
            $aResult = Api::getCatalogData(
                $sSearchText,
                $iOnPage,
                $iPage,
                $this->type,
                $this->search_type,
                $this->search_section,
                $this->bSubsection
            );

            if (!$aResult) {
                $this->setData('not_found', 1);

                return true;
            }

            foreach ($aResult['items'] as &$aObject) {
                $aObject['show_detail'] = (int) !Card::isDetailHiddenByCard($aObject['card']);
            }

            $this->getPageLine(
                $iPage,
                $aResult['count'],
                $this->sectionId(),
                [
                    'search_text' => $sSearchText,
                    'search_type' => $this->type,
                    'search_section' => $this->search_section,
                ],
                ['onPage' => $iOnPage],
                'aPages',
                !$this->isMainModule()
            );

            /* Отправляем данные в модуль */
            $this->setData('result_count', $aResult['count']);
            $this->setData(
                'result',
                site_module\Parser::parseTwig(
                    $this->getTpl(),
                    [
                        'aObjectList' => $aResult['items'],
                        'useMainSection' => true,
                        'useCart' => \skewer\base\site\Type::isShop(),
                        'form_section' => $this->form_section,
                        'quickView' => CatalogViewer\Api::checkQuickView(),
                        'aPages' => $this->getData('aPages'),
                    ],
                    RELEASEPATH . 'build/Page/CatalogViewer/templates/'
                )
            );
        } else {
            $this->setData('not_found', 1);
        }

        return true;
    }

    /**
     * Установка шаблона для вывода поиска по каталогу.
     *
     * @return string
     */
    private function getTpl()
    {
        $aTemplates = [
            'list' => 'SimpleList.twig',
            'gallery' => 'GalleryList.twig',
            'table' => 'TableList.twig',
        ];

        $sTpl = $this->sCatalogListTemplate;

        // проверяем перекрытие из GET
        if ($sView = $this->getStr('view')) {
            $sTpl = $sView;
        }

        // убеждаемся в наличии
        if (!isset($aTemplates[$sTpl])) {
            $sTpl = 'list';
        }

        return $aTemplates[$sTpl];
    }

    /**
     * Получение данных для общего и информационного поиска.
     */
    private function getInfo()
    {
        $iPage = $this->getInt('page', 1);
        $sSearchText = $this->getStr('search_text');
        $iOnPage = (int) $this->onPage;

        if (!empty($sSearchText)) {
            $aItems = Api::getInfoData(
                $sSearchText,
                $iOnPage,
                $iPage,
                $this->type,
                $this->search_type,
                $this->search_section,
                $this->bSubsection,
                $this->iLength
            );
            if (!$aItems) {
                $this->setData('not_found', 1);

                return true;
            }
            /* Отправляем данные в модуль */
            $this->setData('result_count', $aItems['count']);
            $this->setData('aItems', $aItems['items']);
            $this->getPageLine(
                $iPage,
                $aItems['count'],
                $this->sectionId(),
                [
                    'search_text' => $sSearchText,
                    'search_type' => $this->type,
                    'search_section' => $this->search_section,
                ],
                ['onPage' => $iOnPage],
                'aPages',
                !$this->isMainModule()
            );
        } else {
            $this->setData('not_found', 1);
        }

        return true;
    }
}//class
