<?php

namespace skewer\build\Page\Subscribe;

use skewer\base\site\Page;
use skewer\base\site_module;
use skewer\base\SysVar;
use skewer\build\Page\Subscribe\Api as PageApi;
use skewer\build\Page\Subscribe\ar\SubscribeUser;
use skewer\build\Page\Subscribe\ar\SubscribeUserRow;
use skewer\build\Tool\Subscribe\Api as ToolApi;
use skewer\components\forms\FormBuilder;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;

class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    public $sMiniTpl = '';
    public $mini = 0;

    /** @var string шаблон результата подписки */
    public $AnswersTemplate = 'answers.twig';

    /** @var string шаблон формы */
    public $ConfirmTemplate = 'detail.twig';

    public function execute()
    {
        if ($this->oContext->getLabel() != 'content' && $this->sectionId() == \Yii::$app->sections->getValue('subscribe')) {
            return psComplete;
        }
        $sCmd = $this->getStr('cmd');
        $sActionName = 'action' . ucfirst($sCmd);
        if (method_exists($this, $sActionName)) {
            $this->{$sActionName}();
        } else {
            $this->actionInit();
        }

        $this->setData('mini', $this->mini);
        $this->setTemplate($this->ConfirmTemplate);

        return psComplete;
    }

    public function actionInit()
    {
        $subscribeEntity = new SubscribeEntity($this->sectionId(), $this->mini);

        $formBuilder = new FormBuilder(
            $subscribeEntity,
            $this->sectionId(),
            $this->oContext->sLabel
        );

        if ($this->sMiniTpl) {
            $this->setData(
                'forms',
                $subscribeEntity->getOtherTemplateForm(
                    $this->sMiniTpl,
                    $formBuilder->getHash()
                )
            );
        } else {
            $this->setData(
                'forms',
                $formBuilder->getFormTemplate()
            );
        }
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function actionSendSubscribe()
    {
        $subscribeEntity = new SubscribeEntity(
            $this->sectionId(),
            $this->mini,
            $this->getPost()
        );

        $label = $this->get('label') ?: $this->oContext->getLabel();

        $formBuilder = new FormBuilder(
            $subscribeEntity,
            $this->sectionId(),
            $label
        );

        if ($formBuilder->hasSendData() && $formBuilder->validate()) {
            $subscribeEntity->confirm = $this->getKeyConfirm();
            if ($formBuilder->save()) {
                $formBuilder->setLegalRedirect();

                if ($subscribeEntity->confirm !== 1) {
                    Mailer::sendMail(
                        $subscribeEntity->email,
                        PageApi::tagsReplacement(
                            ModulesParams::getByName(
                                'subscribe',
                                'mail.mailTitle'
                            ),
                            $subscribeEntity
                        ),
                        PageApi::tagsReplacement(
                            ModulesParams::getByName(
                                'subscribe',
                                'mail.mailText'
                            ),
                            $subscribeEntity
                        )
                    );

                    Page::setTitle(
                        PageApi::tagsReplacement(
                            ModulesParams::getByName(
                                'subscribe',
                                'mail.resultTitle'
                            ),
                            $subscribeEntity
                        )
                    );
                }
                $ajaxForm = $this->getInt('ajaxForm');

                $sAnswer = $formBuilder->buildSuccessAnswer(
                    $ajaxForm,
                    $this->sectionId(),
                    ['form_section' => $this->sectionId()]
                );

                if (!$ajaxForm) {
                    // Базовая результирующая или сторонняя с пустым адресом результирующей -> редирект на /response
                    if ($formBuilder->canResponse()) {
                        $this->setData('msg', $sAnswer);
                        $this->setData('back_link', 1);
                    // Сторонняя результирующая -> редирект на другую страницу
                    } elseif ($subscribeEntity->formAggregate->result->isExternalResultPage()) {
                        $formBuilder->setRedirect();
                    }
                }

                $this->setData('msg', $sAnswer);
            }
        } else {
            $this->setData('forms', $formBuilder->getFormTemplate());
        }
    }

    /**
     * Получение ключа для режима рассылки с подтверждением
     *
     * @return int|string
     */
    private function getKeyConfirm()
    {
        $modeSubscribe = (int) SysVar::get('subscribe_mode');
        if ($modeSubscribe === ToolApi::WITH_CONFIRM) {
            $iCountKeys = '1';
            while ($iCountKeys != '0') {
                $sKey = ToolApi::getRandKey(150);
                $iCountKeys = SubscribeUser::find()
                    ->where('confirm', $sKey)
                    ->getCount();
            }

            return $sKey;
        }

        return 1;
    }

    public function actionConfirm()
    {
        $this->setTemplate($this->ConfirmTemplate);

        $sCode = $this->getStr('confirm');

        /** @var SubscribeUserRow $oUser */
        $oUser = SubscribeUser::find()
            ->where('confirm', $sCode)
            ->getOne();

        if (!$oUser) {
            //сообщение об ошибке
            $this->setData(
                'msg',
                PageApi::tagsReplacement(ModulesParams::getByName(
                    'subscribe',
                    'mail.errorText'
                ), $oUser)
            );
            Page::setTitle(PageApi::tagsReplacement(ModulesParams::getByName(
                'subscribe',
                'mail.errorTitle'
            ), $oUser));
        } else {
            SubscribeUser::update()
                ->set('confirm', 1)
                ->where('confirm', $sCode)
                ->get();

            $this->setData(
                'msg',
                PageApi::tagsReplacement(ModulesParams::getByName(
                    'subscribe',
                    'mail.successText'
                ), $oUser)
            );
            Page::setTitle(PageApi::tagsReplacement(ModulesParams::getByName(
                'subscribe',
                'successTitle'
            ), $oUser));
        }
    }

    public function actionUnsubscribe()
    {
        $this->setTemplate($this->AnswersTemplate);

        $sEmail = $this->getStr('email');
        $sToken = $this->getStr('token');

        $bEmail = PageApi::checkEmail($sEmail);

        if (md5('unsub' . $sEmail . '010') != $sToken || !$bEmail) {
            $aData['subscriber_not_delete'] = 1;
        } else {
            $aData['subscriber_delete'] = PageApi::delSubscriber($sEmail);
        }
        $sMsg = site_module\Parser::parseTwig(
            $this->AnswersTemplate,
            $aData,
            __DIR__ . '/templates'
        );

        $this->setData('msg', $sMsg);

        return psComplete;
    }

    public function actionUnsubscribe_ajax()
    {
        $sEmail = $this->getStr('email');

        $bEmail = PageApi::checkEmail($sEmail);

        if (!$bEmail) {
            $this->setData('out', 0);
        } else {
            $this->setData('out', PageApi::delSubscriber($sEmail));
        }

        return psRendered;
    }
} //class
