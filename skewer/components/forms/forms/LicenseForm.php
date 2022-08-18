<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\components\auth\CurrentUser;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\entities\FormExtraDataEntity;
use skewer\components\forms\FormBuilder;

/**
 * Class LicenseForm.
 *
 * @property FormExtraDataEntity $extraEntity
 * @property string $text
 * @property int $agree
 */
class LicenseForm extends ExtraDataForm
{
    /** При валадации js после отправки формы идёт проверка на это наименование */
    const AGREE_STRING_NAME = 'agreed';

    //соглашение на обработку персональных данных
    private $_agree;

    private $_text;

    protected $_extraFields = [
        'text' => 'agreed_title',
    ];

    public function __construct(int $idForm = null, array $config = [])
    {
        parent::__construct($idForm, $config);

        $this->setAgree(
            $this->_formEntity instanceof FormEntity
                ? $this->_formEntity->agree
                : 1
        );

        $text = $this->_extraEntity instanceof FormExtraDataEntity
            ? $this->_extraEntity->agreed_title
            : \Yii::t('forms', 'agreement_title');

        $this->setText($text);
    }

    public function rules()
    {
        return [
            [['agree'], 'integer'],
            [['text'], 'string'],
        ];
    }

    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->_text = $text;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->_text;
    }

    public function setAgree(int $agree)
    {
        $this->_agree = $agree;
    }

    public function getAgree(): int
    {
        return $this->_agree;
    }

    /**
     * Данные по соглашению на обработку персональных данных.
     *
     * @param string $formHash
     * @param TemplateForm $template
     * @param bool $requiredFields
     */
    public function setFormDisplayOptions(
        string $formHash,
        TemplateForm &$template,
        bool $requiredFields = false
    ) {
        if ($this->agree) {
            $license = Parser::parseTwig(
                'license.twig',
                [
                    'formHash' => $formHash,
                    'agreedTitle' => $this->text,
                    'privacyPolicySection' => \Yii::$app->sections->getValue(
                        'privacy_policy'
                    ),
                    self::AGREE_STRING_NAME => $template->getInnerParamValue(self::AGREE_STRING_NAME),
                    'currentUser' => CurrentUser::isAuthorized(),
                    'checkActiveFormDefault' => (bool) SysVar::get('Page.consent_to_processing'),
                ],
                FormBuilder::PATH_FORM_TEMPLATES
            );

            $template->license = $license;
        }
    }

    /**
     * Проверка согласия пользователя.
     *
     * @param array $innerData
     * @param string $formHash
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validateInnerData(array $innerData, string $formHash): bool
    {
        if ($this->agree) {
            if (!isset($innerData[self::AGREE_STRING_NAME]) || empty($innerData[self::AGREE_STRING_NAME])) {
                throw new \Exception(\Yii::t('forms', 'error_no_agreement'));
            }
        }

        return true;
    }
}
