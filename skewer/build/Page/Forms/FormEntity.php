<?php

declare(strict_types=1);

namespace skewer\build\Page\Forms;

use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\TemplateLetter;
use skewer\components\forms\components\typesOfValid\Email;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\helpers\Mailer;

/**
 * Построитель и обработчик кастомных форм
 *
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class FormEntity extends BuilderEntity
{
    public $cmd = 'send';
    public $LetterTemplate = 'letter.twig';
    public $redirectKeyName = 'formBuilder';

    /** @var null|int id записи в базе */
    public $idFormOrder;

    public static $sRichGoals = '';

    /** @var int $_idSection */
    private $_idSection;

    private $_defaultUrl = '';
    private $_tagAction = '';

    private $_resultExecuteHandlers;

    private static $_tableName = '';

    public function __construct(
        FormAggregate $formAggregate,
        int $idSection,
        array $innerData = [],
        array $config = []
    ) {
        static::$_tableName = $formAggregate->settings->slug;
        $this->_idSection = $idSection;

        parent::__construct($innerData, $config);
    }

    public static function tableName(): string
    {
        return static::$_tableName;
    }

    public static function createTable()
    {
    }

    /**
     * Установка дефолтных параметров для корректной работы шаблона.
     *
     * @param string $defaultUrl
     * @param string $tagAction
     * @param bool $ajaxForm
     */
    public function setParamsForTemplate(
        string $defaultUrl = '',
        string $tagAction = ''
    ) {
        $this->_defaultUrl = $defaultUrl;
        $this->_tagAction = $tagAction;
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        return $this->formAggregate->handler->saveData($this, $this->_idSection)
            && parent::save();
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        if ($this->_tagAction) {
            $templateForm->tagAction = $this->_tagAction;
        } else {
            $tagAction = Tree::getSectionAliasPath(
                $this->_idSection,
                true
            ) . $this->_defaultUrl . 'response/';
            if (!$this->formAggregate->result->isExternalResultPage()) {
                $tagAction .= '#form-answer';
            }
            $templateForm->tagAction = $tagAction;
        }

        if (self::$sRichGoals) {
            $templateForm->reachGoals = self::$sRichGoals;
        }
    }

    /**
     * Установить результат выполнения обработчиков формы.
     *
     * @param $result mixed
     */
    private function setResultHandlers($result)
    {
        $this->_resultExecuteHandlers = $result;
    }

    /**
     * Получить результат выполнения обработчиков формы.
     *
     * @return mixed
     */
    public function getResultHandlers()
    {
        return $this->_resultExecuteHandlers;
    }

    /**
     * Отправление результатов формы письмом
     *
     * @param $sLetterTemplate
     * @param $sLetterDir
     * @param bool $bSendAllInfo
     *
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function send2Mail(
        $sLetterTemplate,
        $sLetterDir,
        $bSendAllInfo = false
    ) {
        // Если в форме(шаблон!) не задано значение обработчика(куда отсылаем!), берем системный e-mail
        if (!$this->formAggregate->handler->value) {
            $this->formAggregate->handler->value = Site::getAdminEmail();
        }

        $sMailTo = $this->findEmailField();

        // Посылаем e-mail админу
        $bRes = self::sendMail($sLetterTemplate, $sLetterDir, $bSendAllInfo);

        $this->setResultHandlers($bRes);

        // отправляем уведомление об отправки сообщения - автоответ
        if ($bRes && $this->formAggregate->answer->answer && $sMailTo) {
            Mailer::sendMail(
                $sMailTo,
                $this->formAggregate->answer->title,
                $this->formAggregate->answer->letter
            );
        }

        return $bRes;
    }

    /**
     * Отправка письма с парсингом шаблона.
     *
     * @param string $sLetterTemplate
     * @param string $sTemplateDir
     * @param bool $bSendAllInfo - игнорирование настроек формы на отправку файла
     *
     * @return bool
     */
    private function sendMail(
        $sLetterTemplate = 'letter.twig',
        $sTemplateDir = '',
        $bSendAllInfo = false
    ) {
        if (!$sLetterTemplate) {
            return false;
        }
        if (!$this->formAggregate->handler->value) {
            return false;
        }

        /** Уведомление админу о заполнении формы на сайте */
        $noSendDataInLetter = $bSendAllInfo
            ? (int) !$bSendAllInfo
            : $this->formAggregate->settings->noSendDataInLetter;

        $title = $this->formAggregate->settings->title;

        /** Строка для верхней части письма */
        $sIntroduction = \Yii::t('data/forms', 'mail_adm_new_letter_1');
        $aParams['name_form'] = $title;
        $aParams[\Yii::t('app', 'site_link')] = Site::httpDomain();

        /** ссылка на запись в админке */
        $sShowLinkAdd = $this->idFormOrder ? \Yii::t('data/forms', 'mail_adm_new_letter_2') : '';
        if ($sShowLinkAdd) {
            $sParamSend = $this->idFormOrder ? $this->formAggregate->idForm . '_' . $this->idFormOrder : $this->formAggregate->idForm;
            $aParams['link'] = Site::admUrl('FormOrders', 'tools', $sParamSend);
            $aParams['name'] = Site::getSiteTitle();
        }

        $bTableHide = $this->formAggregate->handler->isMailType();

        $aData = $this->getInnerParams();

        foreach ($this->fields as $field) {
            if (isset($aData[$field->settings->slug])) {
                $field->value = $field->type->getFieldObject()->getValueForLetter(
                    $field->value,
                    $field->type->default
                );
            }
        }

        if (!$noSendDataInLetter) {
            // add attach file
            $aAttachFile = [];
            foreach ($this->fields as $field) {
                $extraData = $field->type->getFieldObject()->getExtraData($field->settings->slug);
                $nameFile = $this->getInnerParamByName($field->settings->slug) ?: $field->getValue();
                if ($extraData && $nameFile) {
                    $aAttachFile[$nameFile] = $extraData;
                }
            }
        }

        $sReplyTo = $this->getMailFrom();

        $templateLetter = new TemplateLetter(
            $this->formAggregate,
            $this->fields
        );
        $templateLetter->tableHide = $bTableHide;

        $sBody = $templateLetter->getBodyForLetter(
            $sIntroduction,
            $sShowLinkAdd
        );

        try {
            if (!$noSendDataInLetter && count($aAttachFile)) {
                return Mailer::sendMailWithAttach(
                    $this->formAggregate->handler->value,
                    $title,
                    $sBody,
                    $aParams,
                    $aAttachFile,
                    $sReplyTo
                );
            }

            return Mailer::sendMail(
                $this->formAggregate->handler->value,
                $title,
                $sBody,
                $aParams,
                $sReplyTo
                );
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Установка email адреса в адрес отправителя для администратора.
     *
     * @return mixed|string
     */
    private function getMailFrom()
    {
        if ($this->formAggregate->settings->emailInReply) {
            $aGoodMailName = ['email', 'e-mail'];
            foreach ($aGoodMailName as $sName) {
                $innerParam = $this->getInnerParamByName($sName);
                if ($innerParam) {
                    return $innerParam;
                }
            }
        }

        return '';
    }

    /**
     * Поиск значения email пользователя в пришедших данных.
     *
     * @return bool|string
     */
    private function findEmailField()
    {
        $result = false;

        foreach ($this->fields as $field) {
            if (get_class($field->type->getTypeOfValidObject()) === Email::class) {
                $innerValue = $this->getInnerParamByName($field->settings->slug);
                return $innerValue ?: $field->type->default;
            }
        }

        return $result;
    }
}
