<?php

declare(strict_types=1);

namespace skewer\components\forms;

use skewer\base\ft\Editor;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\build\Page\Cart\OrderOneClickEntity;
use skewer\components\catalog\Api as CatalogApi;
use skewer\components\catalog\Card;
use skewer\components\catalog\GoodsSelector;
use skewer\components\ecommerce\Api as EcommerceApi;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\entities\FormLinkEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\LicenseForm;
use skewer\components\forms\forms\TypeFieldForm;
use skewer\components\forms\service\FormSectionService;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class FormBuilder
 * отвечает за работы форм на клиентской части.
 *
 * @property string $hash
 */
class FormBuilder
{
    /** Шаблон формы по умолчанию */
    const DEF_FORM_TPL = 'form.twig';

    const PATH_FORM_TEMPLATES = RELEASEPATH . 'components/forms/templates';

    const MAX_LENGTH_FORM_NAME = 60;

    const NAME_LEGAL_REDIRECT = 'legal_redirect';

    /** @var string путь до директории, в которой лежит шаблон */
    public $pathDirByTmp;

    /** @var BuilderEntity $_entity */
    private $_entity;

    /** @var int $_idSection */
    private $_idSection;

    /** @var string $_label */
    private $_label = 'out';

    private $formTemplate = '';

    /** @var string $templateDir папка, хранящая представление */
    private $templateDir = 'templates';

    private $_hash;

    /** @var string Url страницы */
    private $_url;

    /**
     * FormBuilder constructor.
     *
     * @param BuilderEntity $entity
     * @param null|int $idSection
     * @param string $label
     */
    public function __construct(
        BuilderEntity $entity,
        int $idSection = null,
        string $label = 'out'
    ) {
        $this->_entity = $entity;
        $this->_idSection = $idSection;
        $this->_label = $label;
        $this->_url = \Yii::$app->request->url;

        $this->pathDirByTmp = self::PATH_FORM_TEMPLATES;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        try {
            if (!FormEntity::hasFormWithSlug($this->_entity::tableName())
                && !$this->_entity->formAggregate->settings->system
                && $this->canSendFormInSection(
                    $this->_idSection,
                    $this->_entity->formAggregate->idForm,
                    (bool) $this->_entity->formAggregate->settings->system
                )
            ) {
                throw new Exception(
                    \Yii::t('forms', 'no_form', [$this->_entity::tableName()])
                );
            }

            if (!$this->_entity->validate($this->getHash())) {
                throw new Exception(
                    current($this->_entity->getErrors())
                );
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save()
    {
        return $this->canSendData() && $this->_entity->save();
    }

    /**
     * Сборка основного шаблона для форм
     *
     * @param bool $ajaxForm
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return string
     */
    public function getFormTemplate(bool $ajaxForm = false)
    {
        /* очистка ключа, который мог установиться при отправке данных */
        if (\Yii::$app->session->get($this->getSessionFlashKeyName(), null)) {
            \Yii::$app->session->remove($this->getSessionFlashKeyName());
        }

        $template = new TemplateForm($this->_entity);
        $this->_entity->formAggregate->setFormDisplayOptions(
            $this->getHash(),
            $template
        );

        $this->_entity->setAddParamsForShowForm($template);

        $template->formHash = $this->_hash;
        $template->label = $this->_label;
        $template->section = $this->_idSection;
        $template->url = $this->_url;
        $template->ajaxForm = $this->_entity->formAggregate->result->isPopupResultPage() ?: $ajaxForm;

        $template->button = $this->getButton($template->paramsForButtonTemplate);
        $template->input = $this->getInput($template);

        $tmpForm = $this->formTemplate
            ?: $this->_entity->formAggregate->settings->template ?: self::DEF_FORM_TPL;

        return Parser::parseTwig(
            $tmpForm,
            ['templateForm' => $template],
            $this->pathDirByTmp
        );
    }

    public function setFormTemplate($formTemplate)
    {
        $this->formTemplate = $formTemplate;
    }

    /**
     * Получение кнопки.
     *
     * @param array $params
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    protected function getButton(array $params = [])
    {
        $params += [
            'form_button' => $this->_entity->formAggregate->settings->button,
        ];

        return $this->getPartForm('button.twig', $params);
    }

    /**
     * Получение наборов input.
     *
     * @param TemplateForm $templateForm
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getInput(TemplateForm $templateForm)
    {
        $params = $templateForm->paramsForInputTemplate + [
                'templateForm' => $templateForm,
                'form_id' => $this->_entity->formAggregate->idForm,
                'rules' => $this->getRules(),
            ];

        return $this->getPartForm('input.twig', $params);
    }

    /**
     * @param $nameTemplate
     * @param array $params
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    private function getPartForm(string $nameTemplate, array $params)
    {
        $sNameClass = $this->_entity->getNameClassForTemplate($nameTemplate);
        $template = \DIRECTORY_SEPARATOR . $this->templateDir;

        if (file_exists(
            $this->_entity->getDirPath() . $template . \DIRECTORY_SEPARATOR . $sNameClass
        )
        ) {
            $sTemplateDir = $this->_entity->getDirPath() . $template;
        } else {
            $sTemplateDir = __DIR__ . \DIRECTORY_SEPARATOR . $this->templateDir;
            $sNameClass = $nameTemplate;
        }

        return Parser::parseTwig($sNameClass, $params, $sTemplateDir);
    }

    /**
     * Уникальный хеш код формы на странице.
     *
     * @return string
     */
    public function getHash()
    {
        if (empty($this->_hash)) {
            $this->setHash();
        }

        return $this->_hash;
    }

    public function setHash()
    {
        $this->_hash = md5(
            md5($this->_label . $this->_entity->formAggregate->idForm) . $this->_idSection
        );
    }

    public function hasSendData(): bool
    {
        return $this->_entity->hasSendData();
    }

    /**
     * Правила для валидации формы.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getRules()
    {
        $fields = $this->_entity->getFields();
        $aRules = [];

        /* @var FieldAggregate $field */
        foreach ($fields as &$field) {
            $ruleForField = [];
            $ruleForField['required'] = (bool) $field->settings->required;

            $ruleForField += $field->getValidateRules();

            if ($field->type->needAddRuleInValidation()) {
                $ruleForField[mb_strtolower($field->type->typeOfValid)] = true;
            }

            $aRules['rules'][$field->settings->slug] = $ruleForField;
        }

        if ($this->_entity->formAggregate->protection->captcha) {
            $aRules['rules']['captcha'] = [
                'required' => 1,
                'maxlength' => 50,
                'digits' => 1,
            ];
        }

        //Если есть галочка соглашения
        if ($this->_entity->formAggregate->license->agree) {
            $aRules['rules'][LicenseForm::AGREE_STRING_NAME] = [
                'required' => true,
            ];
        }

        return json_encode($aRules);
    }

    /**
     * Проверка существавания раздела, в которую отправляется форма,
     * и привязки формы к разделу
     * (системные формы могут быть не привязаны к разделу).
     *
     * @param int $idSection
     *
     * @throws Exception
     * @throws \Exception
     *
     * @return bool
     */
    private function canSendFormInSection(
        int $idSection,
        int $idForm,
        bool $systemForm
    ): bool {
        $section = Tree::getSection($idSection, false, true);
        if ($section === null) {
            throw new Exception(\Yii::t('forms', 'can_not_send_data'));
        }

        if ($section instanceof TreeSection) {
            $service = new FormSectionService($idSection);
            $formsInSection = $service->get4Section();
            if (isset($formsInSection[$idForm]) || $systemForm) {
                return true;
            }
        }

        return false;
    }

    public function setLegalRedirect()
    {
        \Yii::$app->session->setFlash(self::NAME_LEGAL_REDIRECT, true, false);
        \Yii::$app->session->set($this->getSessionFlashKeyName(), true);
    }

    /**
     * Проверка на ранее отправку формы.
     *
     * @return bool
     */
    public function canSendData()
    {
        $keySession = $this->getSessionFlashKeyName();
        if (\Yii::$app->session->get($keySession)) {
            \Yii::$app->session->remove($keySession);

            $url = str_replace('response/', '', \Yii::$app->request->url);
            $resultPage = Site::httpDomainSlash() . $url;
            \Yii::$app->response->redirect($resultPage)->send();

            return false;
        }

        return true;
    }

    public function getSessionFlashKeyName(): string
    {
        $formId = $this->_entity->formAggregate->idForm;

        return "{$this->_entity->redirectKeyName}_{$formId}_redirect";
    }

    /**
     * Вернет html результирующей страницы успешной отправки.
     *
     * @param $ajaxForm - форма отправляется через ajax?
     * @param $idSection - id раздела
     * @param array $params
     *
     * @throws \Exception
     *
     * @return string
     */
    public function buildSuccessAnswer(
        bool $ajaxForm,
        int $idSection,
        array $params = []
    ) {
        // Текст для форм со сторонней результирующей редактируется в разделе на который идёт перенаправление
        if (!$ajaxForm
            && $this->_entity->formAggregate->result->isExternalResultPage()
            && $this->_entity->formAggregate->result->getFormRedirect(true)) {
            return '';
        }

        $data = ['form_section' => $idSection];

        if (method_exists($this->_entity, 'getSuccessAnswer')) {
            $data['SuccAnswer'] = $this->_entity->getSuccessAnswer();
        }

        // Строим js-скрипт ричголов
        $data['reachGoals'] = $this->_entity->formAggregate->target->buildScriptTargetsInForm();

        // Альтернативный текст ответа пользователю
        if (!isset($data['SuccAnswer']) && $successAnswer = $this->getFormSuccessAnswer(true)) {
            $data['SuccAnswer'] = $successAnswer;
        }

        $data[$ajaxForm ? 'successAjax' : 'success'] = 1;

        if ($this->_entity->formAggregate->result->isPopupResultPage()) {
            $data['popup_result_page'] = true;
        } else {
            $data['form_anchor'] = true;
        }

        if (CatalogApi::isIECommerce()) {
            //Передача e-commerce данных после отправка формы "Купить в один клик"
            if ($this->_entity->formAggregate->settings->slug == OrderOneClickEntity::tableName()) {
                $data['ecommerce'] = EcommerceApi::buildScriptPurchase(
                    $this->_entity->formAggregate->idForm,
                    $idSection,
                    true
                );
            }
        }

        $data += $params;

        $data['check_back'] = $this->_entity->formAggregate->settings->showCheckBack;

        return Parser::parseTwig('answer.twig', $data, __DIR__ . '/templates');
    }

    /**
     * Получить ответ в случае успешной отправки формы из админки.
     *
     * @param bool $parse
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getFormSuccessAnswer(bool $parse = false)
    {
        $successAnswer = trim($this->_entity->formAggregate->result->text);

        if ($parse) {
            // Замена имён полей в шаблоне на заполенные данные пользователя
            $aReplaceData = [];
            foreach ($this->_entity->formAggregate->fields as $field) {
                $aReplaceData["[{$field->settings->slug}]"] = $field->value;
            }

            $successAnswer = strtr($successAnswer, $aReplaceData);
        }

        return $successAnswer;
    }

    /**
     * Базовая результирующая или сторонняя с пустым адресом результирующей
     * -> редирект на /response.
     *
     * @return bool
     */
    public function canResponse(): bool
    {
        return
            $this->_entity->formAggregate->result->isBaseResultPage()
            || (
                $this->_entity->formAggregate->result->isExternalResultPage()
                && !$this->_entity->formAggregate->result->getFormRedirect(true)
            );
    }

    /**
     * Сторонняя результирующая -> редирект на другую страницу.
     */
    public function setRedirect()
    {
        \Yii::$app->session->setFlash(
            'form_source',
            $this->_entity->formAggregate->idForm
        );
        \Yii::$app->getResponse()->redirect(
            $this->_entity->formAggregate->result->getFormRedirect(true),
            '301'
        );
    }

    public function getLastModifyData(): string
    {
        return $this->_entity->formAggregate->getLastModifyData();
    }

    /**
     * Заполнение полей привязанных к товарным позициям
     *
     * @param $iObjectId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function fillGoodsFields($iObjectId)
    {
        $goods = GoodsSelector::get($iObjectId, Card::DEF_BASE_CARD, true);
        if (!$goods) {
            return false;
        }

        $aLinks = FormLinkEntity::getLinksByIdForm($this->_entity->formAggregate->idForm);

        if ($aLinks && count($aLinks)) {
            foreach ($aLinks as $oLink) {
                $sFieldName = $oLink['card_field'];
                $value = $this->getValueGoods($goods, $sFieldName);

                /* @var FieldAggregate $field */
                foreach ($this->_entity->fields as $field) {
                    if ($field->settings->slug == $oLink['form_field']) {
                        $field->value = $value;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Возвращает значение поля товара.
     *
     * @param array $aGoods
     * @param string $sFieldName
     *
     * @return string
     */
    private function getValueGoods(array $aGoods, string $sFieldName)
    {
        $type = ArrayHelper::getValue($aGoods, "fields.{$sFieldName}.type");
        if (in_array($type, [
                Editor::SELECT,
                Editor::COLLECTION,
                Editor::MULTICOLLECTION,
                Editor::MULTISELECT,
            ]) !== false) {
            $sValue = ArrayHelper::getValue(
                $aGoods,
                "fields.{$sFieldName}.tab",
                ''
            );
        } else {
            $sValue = ArrayHelper::getValue(
                $aGoods,
                "fields.{$sFieldName}.value",
                ''
            );
        }

        return $sValue;
    }
}
