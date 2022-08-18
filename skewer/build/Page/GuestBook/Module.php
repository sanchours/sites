<?php

namespace skewer\build\Page\GuestBook;

use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\build\Page\CatalogViewer\Module as CatalogViewerModule;
use skewer\build\Page\CatalogViewer\State\DetailPage;
use skewer\build\Tool\LeftList\Group;
use skewer\build\Tool\Review\Api;
use skewer\components\catalog\GoodsSelector;
use skewer\components\forms\FormBuilder;
use skewer\components\GalleryOnPage;
use skewer\components\microdata;
use yii\helpers\ArrayHelper;

/**
 * Пользовательский модуль отзывов
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    /** @var null Имя таба */
    public $sTabName;

    /** @var int Номер страницы из модуля CatalogViewer - DetailPage */
    public $iPage = 0;

    /** @var int id товара */
    public $objectId;

    /** @var string Указывает тип родитеской сущности */
    public $className = '';

    /** @var bool Если =true - выводится блок отзывов, если =false - отзывы с пагинатором */
    public $showList = false;

    /** @var string Заголовок блока отзывов */
    public $titleOnMain = '';

    /** @var int Максимальная длина отзыва */
    public $maxLen = 500;

    /** @var int Ид раздела из которого будут выбраны отзывы */
    public $section_id;

    /** @var int Количество записей на главной странице */
    public $onPage = 10;

    /** @var int Количество записей на внутренних страницах */
    public $onPageContent = 10;

    /** @var bool Выводить сначала отзывы, а затем форму(при =0)? */
    public $revert = 0;

    /** @var int Не выводить форму? */
    public $hide_form = 0;

    /** @var bool Отдать только микроразметку? */
    public $bOnlyMicrodata = false;

    /** @var int Показать звёзды голосования в списке отзывов? Если значение <0, то параметр будет браться глобальный параметр */
    public $rating = -1;

    /** @const int Количество отзывов, выводимых на детальную товара */
    const onPageGoodsReviews = 10;

    /** @var string Шаблон списка отзывов. Если шаблон не указан, то он определяется динамически */
    public $template = '';

    /** @var string шаблон детального состояния */
    public $template_detail = 'view.twig';

    /** @var string Тип вывода */
    public $typeShow;

    /**
     * {@inheritdoc}
     */
    protected function onCreate()
    {
        if ($this->showList) {
            $this->setUseRouting(false);
        }
    }

    public function init()
    {
        //Номер текущей страницы
        $this->iPage = ($this->iPage) ? $this->iPage : $this->getInt('page', 1);

        // Количество записей на странице(в блоке)
        $this->onPage = ($this->sectionId() != \Yii::$app->sections->main())
            ? abs($this->onPageContent)
            : abs($this->onPage);

        $this->maxLen = ($this->maxLen) ? abs($this->maxLen) : 500;

        $this->setParser(parserTwig);

        // Если параметр не задан строго для раздела, то взять глобальную настройку парамера rating
        if ($this->rating < 0) {
            $this->rating = $this->showRating();
        }

        if ($this->bOnlyMicrodata) {
            $this->set('cmd', 'getMicroData');
        } elseif (
            !$this->get('cmd')
            || (
                !$this->get('cmd')
                &&
                $this->zoneType != Group::CONTENT
                &&
                !$this->get('parent_class')
            )
        ) {
            $this->set('cmd', 'Init');
        }
    }

    public function actionIndex()
    {
        $this->actionInit();
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function actionInit()
    {
        \Yii::$app->router->setLastModifiedDate(GuestBook::getMaxLastModifyDate());

        // Блок отзывов на главной и других страницах(без пагинатора и формы)
        if ($this->showList) {
            $this->setReviewsBlock();
        // Отзывы в разделе, отзывы в табах товара(с пагинатором и формой)
        } else {
            $this->setReviewsWithPaginator();
            $this->setForm();
        }

        return psComplete;
    }

    /**
     * Установить список отзывов с пагинатором
     * Используется:
     * 1. в разделе, образованном от шаблона "Отзывы"
     * 2. в табах товара.
     */
    private function setReviewsWithPaginator()
    {
        $iTotalCount = 0;

        $aReviews = Api::getReviewList(
            $this->className,
            $this->objectId ? $this->objectId : $this->sectionId(),
            $this->iPage,
            $this->onPage,
            $iTotalCount
        );
        $this->setPaginatorPage($aReviews, $this->iPage, $iTotalCount);

        if ($this->template) {
            $sTemplate = $this->template;
        } else {
            if ($this->zoneType == Group::CONTENT) {
                $sTemplate = Api::$aTypeShowReviews[Group::CONTENT]['list']['file'];
            } else {
                $sTemplate = $this->getTemplateReview();
            }
        }

        $oBundle = Asset::register(\Yii::$app->view);

        $sViewReviews = site_module\Parser::parseTwig(
            $sTemplate,
            [
            'items' => $aReviews,
            'gallerySettings_review' => $this->getSettingsGalOnPage(),
            'web_path_svg' => $oBundle->baseUrl . \DIRECTORY_SEPARATOR . 'svg',
            'show_rating' => $this->rating,
            'showGallery' => $this->showGallery(),
            'microData' => $this->objectId ? '' : microdata\reviews\Api::buildHtml4SectionReviews($aReviews),
            'bIsReview4Good' => $this->objectId,
            'aPages' => $this->getData('aPages'),
        ],
            __DIR__ . '/templates'
        );

        $this->setData('sViewReviews', $sViewReviews);

        $this->setTemplate($this->template_detail);
    }

    private function showGallery()
    {
        $bHideGallery = SysVar::get('Review.HideGalleryReview', 0);

        return !$bHideGallery;
    }

    /**
     * Доп. адаптивность для шаблона одиночка не должна быть.
     *
     * @return string
     */
    private function getSettingsGalOnPage()
    {
        $sTemplate = $this->getTemplateReview();

        $aTpl2EntityName = [
            'content_carousel.twig' => 'Review',
            'content_gray.twig' => 'ReviewGray',
            'content_bubble.twig' => 'ReviewBubble',
            'content_single.twig' => 'ReviewSingle',
        ];

        if (!isset($aTpl2EntityName[$sTemplate])) {
            return false;
        }

        $sEntityName = $aTpl2EntityName[$sTemplate];

        $sSettings = GalleryOnPage\Api::getSettingsByEntity($sEntityName, true);

        return $sSettings;
    }

    /**
     * Установить данные пагинатора.
     *
     * @param array $aData - массив отзывов
     * @param int $iPage - номер текущей страницы
     * @param int $iTotalCount - общее количество записей
     */
    private function setPaginatorPage($aData, $iPage, $iTotalCount)
    {
        $aURLParams = [];
        // параметры для построение пагинации для табов
        if ($this->sTabName !== null) {
            $aURLParams = [
                'goods-alias' => GoodsSelector::getGoodsAlias($this->objectId),
                'tab' => $this->sTabName,
            ];

            $this->oContext->sClassName = CatalogViewerModule::className();
        }
        // генерируем пагинацию только когда есть данные
        if (count($aData)) {
            $bHideCanonicalPagination = $this->bOnlyMicrodata || !$this->isMainModule();
            $this->getPageLine(
                $iPage,
                $iTotalCount,
                $this->sectionId(),
                $aURLParams,
                ['onPage' => $this->onPage],
                'aPages',
                $bHideCanonicalPagination
            );
        }
    }

    /**
     * Отправка формы отзывов.
     *
     * @throws \Exception
     *
     * @return int
     */
    public function actionSendReview()
    {
        $post = $this->getPost();
        foreach ($post as &$psValue) {
            $psValue = strip_tags($psValue);
        }

        $reviewEntity = new ReviewEntity($this->sectionId(), $post);
        $reviewEntity->setParamForGoodReview($this->objectId, $this->className);

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $reviewEntity,
            $this->sectionId(),
            $label
        );

        $ajaxForm = $this->getData('ajax') ?: $reviewEntity->formAggregate->result->isPopupResultPage();

        if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
            $formBuilder->setLegalRedirect();

            $aParam = ['form_section' => $this->sectionId()];
            $aParam['answer_review'] = !$ajaxForm && $reviewEntity->isGoodReview();

            $sAnswer = $formBuilder->buildSuccessAnswer(
                $ajaxForm,
                $this->sectionId(),
                $aParam
            );

            if (!$ajaxForm) {
                if ($formBuilder->canResponse()) {
                    $this->setData('msg', $sAnswer);
                    $this->setData('back_link', 1);
                // Сторонняя результирующая -> редирект на другую страницу
                } elseif ($reviewEntity->formAggregate->result->isExternalResultPage()) {
                    $formBuilder->setRedirect();
                }
            }
            $this->setData('msg', $sAnswer);
            $this->setData('back_link', 1);
        } else {
            $this->setData('form', $formBuilder->getFormTemplate());
            if (!$ajaxForm) {
                $this->setReviewsWithPaginator();
            }
        }

        $this->setTemplate($this->template_detail);

        return psComplete;
    }

    /**
     * Получить микроразметку отзывов.
     */
    public function actionGetMicroData()
    {
        $iTotalCount = 0;
        $aData = Api::getReviewList(
            $this->className,
            $this->objectId ? $this->objectId : $this->sectionId(),
            $this->iPage,
            $this->onPage,
            $iTotalCount
        );
        $this->setOut(microdata\reviews\Api::buildHtml4GoodReviews($aData));
        $this->set('cmd', '');

        return psComplete;
    }

    /**
     * Показывать форму?
     *
     * @return bool
     */
    public function bShowForm()
    {
        return ((($this->getLabel() == DetailPage::LABEL_GOODSREVIEWS) && !$this->bOnlyMicrodata) || $this->getLabel() == 'content') && !$this->hide_form;
    }

    /**
     * Установить форму.
     *
     * @throws \Exception
     */
    public function setForm()
    {
        if (!$this->bShowForm()) {
            return;
        }

        $reviewEntity = new ReviewEntity($this->sectionId());
        $reviewEntity->setParamForGoodReview($this->objectId, $this->className);

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $reviewEntity,
            $this->sectionId(),
            $label
        );

        $this->setData('form', $formBuilder->getFormTemplate());
        $this->setData('revert', $this->revert);
    }

    /**
     * Установить блок отзывов. Используется на главной и других страницах.
     */
    public function setReviewsBlock()
    {
        if (!$this->onPage) {
            return;
        }

        $oBundle = Asset::register(\Yii::$app->view);

        $aData = Api::getArrayReviews(
            $this->onPage,
            $this->sectionId(),
            $this->section_id
        );

        if (empty($aData)) {
            return psComplete;
        }

        $this->setData('title', $this->titleOnMain);
        $this->setData('items', $aData);
        $this->setData('maxLen', $this->maxLen);
        $this->setData('showList', $this->showList);
        $this->setData('section_id', $this->section_id);
        $this->setData('gallerySettings_review', $this->getSettingsGalOnPage());
        $this->setData(
            'web_path_svg',
            $oBundle->baseUrl . \DIRECTORY_SEPARATOR . 'svg'
        );
        $this->setData('show_rating', $this->rating);
        $this->setData('showGallery', $this->showGallery());

        if ($this->template) {
            $sTemplate = $this->template;
        } else {
            $sTemplate = $this->getTemplateReview();
        }

        $this->setTemplate($sTemplate);
    }

    /**
     * Получение шаблона отзывов.
     *
     * @return string
     */
    public function getTemplateReview()
    {
        $sZone = ($this->zoneType && $this->zoneType != Group::CONTENT) ? 'column' : Group::CONTENT;

        return ArrayHelper::getValue(
            Api::$aTypeShowReviews,
            [$sZone, $this->typeShow, 'file'],
            ''
        );
    }

    /**
     * Выводить рейтинг в список отзывов?
     *
     * @return int
     */
    public function showRating()
    {
        return SysVar::get('Review.showRating', 0);
    }
}
