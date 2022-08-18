<?php

namespace skewer\build\Page\FAQ;

use skewer\base\site;
use skewer\base\site_module;
use skewer\build\Adm\FAQ as AdmFAQ;
use skewer\build\Design\Zones;
use skewer\components\forms\FormBuilder;
use skewer\components\seo;
use skewer\components\seo\SeoPrototype;
use skewer\components\traits\CanonicalOnPageTrait;
use yii\web\NotFoundHttpException;

/**
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    use CanonicalOnPageTrait;

    /** @var int Количество записей на странице */
    public $onPage = 10;

    /** @var AdmFAQ\models\Faq | null  - запись FAQ */
    private $oFAQRow;

    /** @var bool Выводить сначала форму, а затем список вопросов(при =0)? */
    public $revert = 0;

    /** @var string шаблон детального состояния */
    public $template_detail = 'detal.twig';

    /** @var string шаблон списка */
    public $template = 'view.twig';

    public function init()
    {
        $this->setParser(parserTwig);
        if (!$this->get('cmd')) {
            $this->set('cmd', 'Index');
        }
    }

    /**
     * Детальная страница вопроса.
     *
     * @param string $alias - псевдоним вопроса
     * @param int $id - id вопроса
     *
     * @throws NotFoundHttpException - в случае, если запись не найдена
     *
     * @return int - статус выполнения процесса
     */
    public function actionDetail($alias = '', $id = 0)
    {
        if (!$alias and !$id) {
            return $this->actionIndex();
        }

        if ($alias) {
            $this->oFAQRow = Api::getFAQByAlias($alias);
        } else {
            $this->oFAQRow = Api::getFAQById($id);
            if (isset($this->oFAQRow['parent'], $this->oFAQRow['alias'])) {
                $this->setCanonicalByAlias(
                    $this->oFAQRow['parent'],
                    $this->oFAQRow['alias']
                );
            }
        }

        $this->setStatePage(Zones\Api::DETAIL_LAYOUT);

        if (!$this->oFAQRow) {
            throw new NotFoundHttpException();
        }
        if (!isset($this->oFAQRow['parent']) || $this->oFAQRow['parent'] != $this->sectionId()) {
            throw new NotFoundHttpException();
        }
        \Yii::$app->router->setLastModifiedDate($this->oFAQRow['last_modified_date']);

        $this->setData('list', ['items' => [$this->oFAQRow]]);

        $this->setTemplate($this->template_detail);

        /* H1 */
        if (isset($this->oFAQRow['content'])) {
            site\Page::setTitle(nl2br(strip_tags($this->oFAQRow['content'])));
        }

        /* Pathline */
        site\Page::setAddPathItem(
            strip_tags($this->oFAQRow['content']),
            Api::getUrl($this->oFAQRow['parent'], $this->oFAQRow['alias'], $this->oFAQRow['id'])
        );

        /* SEO */
        $this->setSeo(new AdmFAQ\Seo(0, $this->sectionId(), $this->oFAQRow));

        return psComplete;
    }

    /**
     * Отправка формы вопрос-ответ
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return int
     */
    public function actionSendFAQ()
    {
        $post = $this->getPost();
        foreach ($post as &$psValue) {
            $psValue = strip_tags($psValue);
        }

        $faqEntity = new FaqEntity($this->sectionId(), $post);
        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $faqEntity,
            $this->sectionId(),
            $label
        );

        $ajaxForm = $this->getInt('ajaxForm');

        if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
            $formBuilder->setLegalRedirect();

            /*Снимаем флаг отправки и выводим сообщение*/
            $answer = $formBuilder->buildSuccessAnswer(
                $ajaxForm,
                $this->sectionId(),
                ['form_section' => $this->sectionId()]
            );

            $answer = $answer ?: \Yii::t('faq', 'ans_success');
            if (!$ajaxForm) {
                if ($formBuilder->canResponse()) {
                    $this->setData('msg', $answer);
                    $this->setData('back_link', 1);

                // Сторонняя результирующая -> редирект на другую страницу
                } elseif ($faqEntity->formAggregate->result->isExternalResultPage()) {
                    $formBuilder->setRedirect();
                }
            }
            $this->setData('msg', $answer);
            $this->setData('back_link', 1);
        } else {
            $this->setData('form', $formBuilder->getFormTemplate());
            if (!$ajaxForm) {
                $page = $this->get('page') ?: 1;
                $this->setItems($page);
            }
        }

        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * * Вывод списка вопросов.
     *
     * @param int $page - номер страницы пагинатора
     *
     * @throws NotFoundHttpException - в случае, если обратились к несуществующей странице пагинатора
     * @throws \Exception
     *
     * @return int - стутус выполнения процесса
     */
    public function actionIndex($page = 1)
    {
        \Yii::$app->router->setLastModifiedDate(AdmFAQ\models\Faq::getMaxLastModifyDate());
        $this->setItems($page);
        $this->setForm();
        $this->setData('revert', $this->revert);
        $this->setData('section', $this->sectionId());

        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * Установить записи в шаблон.
     *
     * @param int $page - номер страницы пагинатора
     *
     * @throws NotFoundHttpException - в случае, если обратились к несуществующей странице пагинатора
     */
    private function setItems($page = 1)
    {
        $iCount = 0;

        $aItems = Api::getItems($this->sectionId(), $page, $this->onPage, $iCount, AdmFAQ\models\Faq::statusApproved);
        $aItems = Api::formattingDate($aItems);

        if ($page != 1 && (!$aItems || !$iCount)) {
            throw new NotFoundHttpException();
        }
        $this->getPageLine($page, $iCount, $this->sectionId(), [], ['onPage' => $this->onPage], 'aPages', !$this->isMainModule());

        $this->setData('list', ['items' => $aItems]);
        $this->setData('section', $this->sectionId());
    }

    /**
     * Установить форму в шаблон.
     *
     * @throws \Exception
     */
    private function setForm()
    {
        $faqEntity = new FaqEntity($this->sectionId());

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $faqEntity,
            $this->sectionId(),
            $label
        );

        $this->setData('form', $formBuilder->getFormTemplate());
    }

    /**
     * Установка seo-данных.
     *
     * @param SeoPrototype $oSeo - объект, заполненный данными вопроса
     */
    public function setSeo(SeoPrototype $oSeo)
    {
        $this->setEnvParam(seo\Api::SEO_COMPONENT, $oSeo);
        $this->setEnvParam(seo\Api::OPENGRAPH, '');
        site\Page::reloadSEO();
    }
}
