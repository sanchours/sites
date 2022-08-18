<?php

namespace skewer\build\Page\Forms;

use skewer\base\log\Logger;
use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module;
use skewer\components\forms\Api as ApiForm;
use skewer\components\forms\components\protection\Captcha;
use skewer\components\forms\FormBuilder;
use skewer\components\forms\service\FormSectionService;
use skewer\components\forms\service\FormService;
use yii\base\UserException;

/**
 * Модуль вывода формы
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    /** Шаблон формы по умолчанию */
    const DEF_FORM_TPL = 'form.twig';

    public $FormTemplate = '';             // Шаблон отображения формы раздела
    public $AnswersTemplate = 'answer.twig'; // Шаблон отображения ответов
    private $ReachGoalTemplate = 'reachgoals.twig';   // Шаблон reachgoals

    public $FormId = 0;
    public $AjaxForm = 0;
    public $tplDir;

    /** @var string Получение формы по имени приоритетнее чем по id. Используется только в ajax-запросе формы */
    private $FormName = '';
    private $sLabel = '';
    private $section = '';

    /** @var FormService $_formService */
    private $_formService;

    /** @var string $_reachGoals */
    private $_reachGoals = '';

    public function init()
    {
        if (!method_exists($this, 'action' . ucfirst($this->get('cmd')))) {
            $this->set('cmd', 'Index');
        }

        if ($this->tplDir) {
            $this->oContext->setModuleDir($this->tplDir);
        }

        $this->setData('moduleWebPath', $this->getModuleWebDir());

        $this->_formService = new FormService();
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function actionIndex()
    {
        $this->setParam();

        if ($this->needShowAnswer()) {
            return $this->actionRedirect();
        }

        return $this->actionShow();
    }

    /**
     * Проверка на передачу параметров для отправки формы.
     *
     * @return bool
     */
    private function needShowAnswer(): bool
    {
        return \Yii::$app->session->get('forms_label') !== null
            && \Yii::$app->session->get('html_answer') !== null
            && (/* Проверяем в той ли мы метке, иначе просто отобразим форму */
                \Yii::$app->session->get('forms_label') == $this->sLabel
                || (!$this->AjaxForm && \Yii::$app->session->get('forms_label') == 'out')
            );
    }

    private function actionRedirect()
    {
        $sAnswer = \Yii::$app->session->get('html_answer');
        $this->setData('answer', $sAnswer);
        $this->setData('form_anchor', true);
        $this->setTemplate($this->AnswersTemplate);

        \Yii::$app->session->remove('forms_label');
        \Yii::$app->session->remove('html_answer');

        return psComplete;
    }

    /**
     * @throws \Exception
     * @throws \yii\base\UserException
     *
     * @return int
     */
    public function actionSend()
    {
        $this->setParam();
        $iFormId = $this->getInt('form_id');
        $sFormPageUrl = $this->getStr('url', '');

        if (!$this->AjaxForm && $this->FormId != $iFormId) {
            return psComplete;
        }

        $this->sLabel = $this->get('label');

        if (!$iFormId) {
            $this->setData('form', \Yii::t('forms', 'ans_not_found'));
            $this->setTemplate(FormBuilder::DEF_FORM_TPL);

            return psComplete;
        }

        $formAggregate = $this->_formService->getFormById($iFormId);

        $formEntity = new FormEntity(
            $formAggregate,
            $this->sectionId(),
            $this->getPost()
        );

        $formBuilder = new FormBuilder(
            $formEntity,
            $this->sectionId(),
            $this->sLabel
        );

        if ($formBuilder->hasSendData() && $formBuilder->validate() && $formBuilder->save()) {
            $sAnswer = $formBuilder->buildSuccessAnswer(
                $this->AjaxForm,
                $this->sectionId(),
                [
                    'form_section' => ($this->section) ? $this->section : $this->sectionId(),
                    'form_page_url' => $sFormPageUrl,
                ]
            );

            // не ajax форма
            if (!$this->AjaxForm) {
                $this->setData('form_anchor', true);
                $formBuilder->setLegalRedirect();

                if ($formBuilder->canResponse()) {
                    \Yii::$app->session->set('html_answer', $sAnswer);
                    \Yii::$app->session->set('forms_label', $this->sLabel);

                    $sNewUrl = str_replace(
                        'response/',
                        '',
                        \Yii::$app->request->pathInfo
                    );

                    $resultPage = site\Site::httpDomainSlash() . $sNewUrl . 'response/';

                    if (!$formEntity->formAggregate->result->isExternalResultPage()) {
                        $resultPage .= '#form-answer';
                    }

                    \Yii::$app->response->redirect($resultPage)->send();

                // Сторонняя результирующая -> редирект на другую страницу
                } elseif ($formEntity->formAggregate->result->isExternalResultPage()) {
                    \Yii::$app->session->setFlash('form_source', $iFormId);
                    \Yii::$app->getResponse()->redirect(
                        $formEntity->formAggregate->result->getFormRedirect(true),
                        '301'
                    )->send();
                }
            }

            $this->setData('answer', $sAnswer);

            if (ApiForm::$sAnswerText !== null) {
                $this->setData('answer', ApiForm::$sAnswerText);
            }

            if (ApiForm::$sRedirectUri !== null) {
                $this->setData('redirect_uri', ApiForm::$sRedirectUri);
            }

            $this->setTemplate($this->AnswersTemplate);
        } else {
            $sTmp = Tree::getSectionAliasPath($this->sectionId(), true);
            $formEntity->setParamsForTemplate('', $sTmp);

            $this->setData(
                'form',
                $formBuilder->getFormTemplate($this->AjaxForm)
            );
            $this->setTemplate(FormBuilder::DEF_FORM_TPL);
        }

        return psComplete;
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function actionShow()
    {
        $this->setParam();

        $objectId = $this->getInt('objectId');
        $sectionId = $this->sectionId();

        if ($this->FormName) {
            $formAggregate = $this->_formService->getFormByName($this->FormName);
        } elseif ($this->FormId) {
            try {
                $formAggregate = $this->_formService->getFormById($this->FormId);
            } catch (UserException $e) {
                Logger::dumpException($e);
                return psBreak;
            }
        } elseif (\Yii::$app->request->post('section')) {
            // Поиск по секции только для ajax форм
            $formSectionService = new FormSectionService(
                \Yii::$app->request->post('section')
            );
            $sectionId = \Yii::$app->request->post('section');
            $formAggregate = $formSectionService->getFormForCurrentSection();
        } else {
            $formAggregate = null;
        }

        $detailUrl = '';
        $mainContent = site\Page::getMainModuleProcess();
        if ($mainContent) {
            if (!$mainContent->isComplete()) {
                return psWait;
            }
            $detailUrl = $mainContent->getUsedURL();
        }

        $this->setReachGoal();
        $this->setData('reachGoals', $this->_reachGoals);

        if ($formAggregate === null || empty($formAggregate)) {
            $this->setTemplate($this->ReachGoalTemplate);
        } else {
            $this->checkResending();
            $formEntity = new FormEntity(
                $formAggregate,
                $sectionId,
                $this->getPost()
            );
            $formEntity->setParamsForTemplate($detailUrl, '');

            $formBuilder = new FormBuilder(
                $formEntity,
                $sectionId,
                $this->sLabel
            );
            \Yii::$app->router->setLastModifiedDate(
                $formBuilder->getLastModifyData()
            );

            /* очистка ключа, который мог установиться при отправке данных */
            \Yii::$app->session->remove($formBuilder->getSessionFlashKeyName());

            if ($objectId) {
                $formBuilder->fillGoodsFields($objectId);
            }

            $formBuilder->setFormTemplate($this->FormTemplate);
            $sTemplate = $formBuilder->getFormTemplate((bool) $this->AjaxForm);
            $this->setData('form', $sTemplate);
            $this->setTemplate($formBuilder::DEF_FORM_TPL);
        }

        return psComplete;
    }

    /**
     * Перенеправление в случае повторной отправки тех же данных формы на response.
     *
     * @return bool
     */
    private function checkResending()
    {
        $startResponse = mb_strpos(
            \Yii::$app->request->pathInfo,
            'response/'
        );

        if (is_int($startResponse)
            && !\Yii::$app->session->getFlash(
                FormBuilder::NAME_LEGAL_REDIRECT
            ) && empty($this->getPost())
        ) {
            $newUrl = mb_substr(
                \Yii::$app->request->pathInfo,
                0,
                $startResponse
            );
            $newUrl = site\Site::httpDomainSlash() . $newUrl;
            \Yii::$app->response->redirect($newUrl)->send();
        }

        return false;
    }

    public function actionCaptchaAjax()
    {
        if (Captcha::check(
            $this->getStr('code'),
            $this->getStr('hash'),
            false
        )) {
            $this->setData('out', 1);
        } else {
            $sMessage = '"' . \Yii::t(
                'forms',
                'captcha_title'
            ) . '" ' . \Yii::t('forms', 'wrong_captcha');
            $this->setData('out', $sMessage);
        }

        return psRendered;
    }

    private function setParam()
    {
        if ($this->section = \Yii::$app->request->post('section')) {
            $this->section = ($aSection = Tree::getSection(
                $this->section,
                true
            )) ? $aSection['id'] : $this->sectionId();
        } else {
            $this->section = $this->sectionId();
        }

        $this->FormName = \Yii::$app->request->post('formName', '');
        $this->AjaxForm = $this->getInt(
            'ajaxForm',
            $this->AjaxForm
        ); // Отправлять форму через ajax
        $this->sLabel = $this->oContext->getLabel();
    }

    /**
     * Перехват целей с отдельной результирующей.
     *
     * @throws \Exception
     *
     * @return null|string
     */
    private function setReachGoal()
    {
        $idFormSource = (int) \Yii::$app->session->getFlash(
            'form_source',
            null,
            true
        );

        if ($idFormSource) {
            $formAggregate = $this->_formService->getFormById($idFormSource);
            $this->_reachGoals = $formAggregate->getScriptTargetsInForm();
        }
    }
}
