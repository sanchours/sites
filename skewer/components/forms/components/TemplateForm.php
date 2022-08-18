<?php

declare(strict_types=1);

namespace skewer\components\forms\components;

use skewer\components\design\TplSwitchForm;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use Mobile_Detect;

/**
 * Class TemplateForm
 * хранение параметров построения интернфейса отображения формы.
 */
class TemplateForm
{
    /** @var string */
    public $formHash;

    /** @var string */
    public $label = 'out';
    /** @var int */
    public $section;

    /** @var int ??? неясно, что это за параметр */
    public $page;

    /** @var bool */
    public $ajaxForm;

    /** @var bool */
    public $blockJs = false;

    /** @var string */
    public $captcha = '';
    /** @var string */
    public $hiddenCaptchaInput = '';

    /** @var string */
    public $license = '';
    /** @var string */
    public $phraseRequiredFields;
    /** @var string */
    public $button;
    /** @var string */
    public $input;
    /** @var string */
    public $reachGoals;
    /** @var string */
    public $addParam;

    /** @var string */
    public $tagAction;

    /** @var array */
    public $errors;
    /** @var array */
    public $rules;

    /** @var string */
    public $url;

    /** @var array Дополнительные параметры для вывода в шаблоне с кнопкой */
    public $paramsForButtonTemplate = [];
    /** @var array Дополнительные параметры для вывода в шаблоне с input параметрами */
    public $paramsForInputTemplate = [];

    /** @var string */
    private $title;

    /** @var string */
    private $cmd = 'send';

    /** @var string */
    private $slug;

    /** @var string */
    private $addClass;

    /** @var string */
    private $method;

    /** @var int */
    private $showHeader;
    /** @var string */
    private $moduleName;
    /** @var bool */
    private $popupResultPage;

    /** @var FieldAggregate[] */
    private $fields;

    /** @var BuilderEntity $_entity */
    private $_entity;

    /**
     * TemplateForm constructor.
     *
     * @param BuilderEntity $entity
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function __construct(BuilderEntity $entity)
    {
        $this->_entity = $entity;

        $this->cmd = $this->_entity->cmd;
        $this->slug = $this->_entity->formAggregate->settings->slug;
        $this->title = $this->_entity->formAggregate->settings->title;
        $this->addClass = $this->_entity->addClass;
        $this->method = $this->_entity->method;
        $this->moduleName = $this->_entity->moduleName;

        $this->popupResultPage = $this->_entity->formAggregate->result->isPopupResultPage();
        $this->showHeader = $this->_entity->formAggregate->settings->showHeader;
        $this->fields = $this->_entity->getFields();
    }

    /**
     * @return FieldAggregate[]
     */
    public function getFields(): array
    {
        return $this->getViewGroup();
    }

    /**
     * Получение переданных из формы значений.
     *
     * @param string $name
     */
    public function getInnerParamValue(string $name)
    {
        return $this->_entity->getInnerParamByName($name);
    }

    /**
     * Добавление тега для группировки полей.
     *
     * @return mixed
     */
    public function getViewGroup()
    {
        $iBegin = 0;
        $sLastKey = '';
        $count = count($this->fields);
        $countCurrent = 1;

        foreach ($this->fields as $sKey => &$field) {
            if ($field->settings->groupPrevField && $sLastKey && !$iBegin) {
                $this->fields[$sLastKey]->settings->groupPrevField = '<div class="form__cols-group js-select-group">';
                $iBegin = 1;
            } elseif (!$field->settings->groupPrevField && $sLastKey && $iBegin) {
                $this->fields[$sLastKey]->settings->groupPrevField = '</div>';
                $iBegin = 0;
            }
            $field->settings->groupPrevField = ($countCurrent == $count && $field->settings->groupPrevField) ? '</div>' : '';
            $sLastKey = $sKey;
            ++$countCurrent;
        }

        return $this->fields;
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return $this->cmd;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getAddClass(): string
    {
        return $this->addClass;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return int
     */
    public function getShowHeader(): int
    {
        return (int) $this->showHeader;
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getPopupResultPage(): bool
    {
        return $this->popupResultPage;
    }

    /**
     * @return mixed
     */
    public function getCommonClass()
    {
        return TplSwitchForm::getCommonClassForm();
    }

    public function getMobile(): bool
    {
        $detect = new Mobile_Detect();

        return $detect->isMobile();
    }

    public function getError()
    {
        return $this->_entity->getErrors();
    }

    public function isErrorField(): bool
    {
        return $this->_entity->hasFieldsError();
    }
}
