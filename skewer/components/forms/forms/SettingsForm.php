<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\base\site_module\Parser;
use skewer\components\forms\components\protection\BlockJs;
use skewer\components\forms\components\protection\HiddenField;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\FormBuilder;
use skewer\components\i18n\Languages;
use skewer\components\i18n\Messages;
use skewer\components\i18n\models\LanguageValues;
use yii\base\UserException;

/**
 * Class SettingsForm
 * основные настройки полей, не имеющие специфической бизнес-логики.
 *
 * @property $slug
 * @property $button
 */
class SettingsForm extends InternalForm
{
    public $slug;
    public $title;

    public $crm;
    public $template;

    /** @var int */
    public $showRequiredFields;
    /** @var int */
    public $showHeader;
    /** @var int */
    public $system;
    /** @var int */
    public $emailInReply;
    /** @var int */
    public $noSendDataInLetter;

    public $class;
    public $showCheckBack;

    private $_slug;
    private $_button;

    public function __construct(array $params = [], array $config = [])
    {
        if (empty($params)) {
            $this->setDefaultValue();
        } else {
            $this->setAttributes($params);
        }

        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [
                [
                    'crm',
                    'showRequiredFields',
                    'showHeader',
                    'system',
                    'emailInReply',
                    'noSendDataInLetter',
                    'showCheckBack',
                ],
                'boolean',
            ],
            [
                ['slug', 'title', 'template', 'button', 'class'],
                'string',
                'max' => 255,
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => \Yii::t('forms', 'param_title'),
            'slug' => \Yii::t('forms', 'param_name'),
        ];
    }

    /**
     * @return null|string
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     * @param null|string $slug
     *
     * @throws UserException
     */
    public function setSlug(string $slug = null)
    {
        if (in_array(
            $slug,
            [HiddenField::$nameHideField, BlockJs::$nameField]
        )) {
            throw new UserException(
                \Yii::t('forms', 'field_identifier_forbidden_use')
            );
        }

        $this->_slug = $slug;
    }

    private function setDefaultValue()
    {
        $this->title = \Yii::t('forms', 'new_form');

        $this->noSendDataInLetter = 1;
        $this->showRequiredFields = 1;
        $this->showCheckBack = 1;
        $this->system = 0;
    }

    /**
     * @return mixed
     */
    public function getButton()
    {
        if ($this->_button) {
            $buttonInfo = explode('.', $this->_button);
            if (isset($buttonInfo[1])) {
                $value = Messages::getByName(
                    $buttonInfo[0],
                    $buttonInfo[1],
                    \Yii::$app->i18n->getTranslateLanguage()
                );
                if ($value instanceof Messages) {
                    $this->_button = $value->value;
                }
            }
        }

        return $this->_button;
    }

    /**
     * @param mixed $button
     */
    public function setButton(string $button = null)
    {
        if ($button && count(explode('.', $button)) !== 2) {
            $currentLang = \Yii::$app->i18n->getTranslateLanguage();
            $allLang = Languages::getAllActive();
            $langValue = Messages::getByName(
                'forms',
                'button_' . $this->slug,
                $currentLang
            );

            if (!$langValue) {
                foreach ($allLang as $lang) {
                    $oParameters = new LanguageValues();
                    $oParameters->value = ($lang['name'] == $currentLang) ? $button : ' ';
                    $oParameters->message = 'button_' . $this->slug;
                    $oParameters->category = 'forms';
                    $oParameters->language = $lang['name'];
                    $oParameters->override = LanguageValues::overrideYes;
                    $oParameters->status = LanguageValues::statusTranslated;
                    $oParameters->save();
                }
            } else {
                if ($langValue->value !== $button) {
                    $langValue->value = $button;
                    $langValue->save();
                }
            }

            $button = 'forms.' . 'button_' . $this->slug;
        }

        $this->_button = $button;
    }

    public function setFormDisplayOptions(
        string $formHash,
        TemplateForm &$template,
        bool $requiredFields = false
    ) {
        if ($requiredFields && $this->showRequiredFields) {
            $template->phraseRequiredFields = Parser::parseTwig(
                'phraseRequiredFields.twig',
                [],
                FormBuilder::PATH_FORM_TEMPLATES
            );
        }
    }
}
