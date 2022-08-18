<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\components\protection\BlockJs;
use skewer\components\forms\components\protection\Captcha;
use skewer\components\forms\components\protection\HiddenField;
use skewer\components\forms\components\TemplateForm;

class ProtectionForm extends InternalForm
{
    /** @var bool $captcha */
    public $captcha;
    /** @var bool $hideField */
    public $hideField;
    /** @var bool $blockJs */
    public $blockJs;

    public function __construct(
        bool $captcha,
        bool $hideField,
        bool $blockJs,
        array $config = []
    ) {
        $this->captcha = $captcha;
        $this->hideField = $hideField;
        $this->blockJs = $blockJs;

        parent::__construct($config);
    }

    public function showCaptcha(): bool
    {
        return $this->captcha === true;
    }

    public function rules()
    {
        return [
            [['captcha', 'hideField', 'blockJs'], 'boolean'],
        ];
    }

    /**
     * @param string $formHash
     * @param TemplateForm $template
     * @param bool $requiredFields
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function setFormDisplayOptions(
        string $formHash,
        TemplateForm &$template,
        bool $requiredFields = false
    ) {
        $template->captcha = $this->captcha ? Captcha::getHtml($formHash) : '';
        $template->hiddenCaptchaInput = $this->hideField ? HiddenField::getHtml() : '';

        if ($this->blockJs) {
            BlockJs::regJs();
            $template->blockJs = true;
        }
    }

    /**
     * @param array $innerData
     * @param string $formHash
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validateInnerData(array $innerData, string $formHash): bool
    {
        if (
            $this->hideField
            && HiddenField::check($innerData)
        ) {
            throw new \Exception(\Yii::t('forms', 'ans_validation'));
        }

        if (
            $this->blockJs
            && BlockJs::check($innerData)
        ) {
            throw new \Exception(\Yii::t('forms', 'ans_validation'));
        }

        if ($this->captcha) {
            if (!isset($innerData['captcha'])) {
                throw new \Exception(\Yii::t('forms', 'send_captcha_error'));
            }

            /* todo Указатель на поле формы с каптчей */
            //$oFieldCaptcha = isset($this->aFieldRows['captcha']) ? $this->aFieldRows['captcha'] : null;

            if (!$innerData['captcha']
                || !Captcha::check($innerData['captcha'], $formHash, true)
            ) {
                // Установить полю сообщение о неверной валидации
                /*todo тут вывод ошибки для капчи, пока не работает
                 * $oFieldCaptcha and $oFieldCaptcha->addError('ans_captcha_error',
                    \Yii::t('forms', 'ans_captcha_error'));*/
                throw new \Exception(\Yii::t('forms', 'ans_captcha_error'));
            }
        }

        return true;
    }
}
