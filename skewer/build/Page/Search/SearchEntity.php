<?php

declare(strict_types=1);

namespace skewer\build\Page\Search;

use skewer\base\section\Tree;
use skewer\base\SysVar;
use skewer\components\auth\Auth;
use skewer\components\forms\components\fields\Input;
use skewer\components\forms\components\fields\Select;
use skewer\components\forms\components\TemplateForm;
use skewer\components\forms\components\typesOfValid\Text;
use skewer\components\forms\entities\BuilderEntity;
use skewer\components\forms\forms\FieldAggregate;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\forms\HandlerTypeForm;
use skewer\components\forms\forms\SettingsFieldForm;
use skewer\components\search;

/**
 * This is parameters of required fields for this form.
 *
 * @property string $search_text
 * @property string $search_type
 * @property int $search_section
 * @property FormAggregate $formAggregate
 * @property FieldAggregate[] $fields
 */
class SearchEntity extends BuilderEntity
{
    protected static $fieldsForCreatedForm = [
        [
            'settings' => [
                'slug' => 'search_text',
                'title' => 'search.phrase',
                'required' => 1,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Input::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'search_type',
                'title' => 'search.criteria',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Select::class,
                'typeOfValid' => Text::class,
            ],
        ],
        [
            'settings' => [
                'slug' => 'search_section',
                'title' => 'search.search_section',
                'required' => 0,
                'labelPosition' => SettingsFieldForm::LABEL_POSITION_TOP,
                'newLine' => 1,
            ],
            'type' => [
                'name' => Select::class,
                'typeOfValid' => Text::class,
            ],
        ],
    ];

    public $cmd = '';
    public $method = 'get';
    public $redirectKeyName = 'mainForm';

    /** @var int */
    private $_idSection;

    public static function tableName(): string
    {
        return 'main_search_form';
    }

    /**
     * @throws \Exception
     * @throws \yii\base\UserException
     */
    public static function createTable()
    {
        $formAggregate = new FormAggregate();
        $formAggregate->settings->title = \Yii::t('search', 'search');
        $formAggregate->settings->slug = self::tableName();
        $formAggregate->settings->system = 1;
        $formAggregate->settings->button = 'search.search';

        $formAggregate->settings->showHeader = 0;
        $formAggregate->protection->captcha = false;
        $formAggregate->license->agree = 0;

        $formAggregate->handler->type = HandlerTypeForm::HANDLER_TO_METHOD;

        $formAggregate->save();
        $formAggregate->saveExtraData();

        self::createFields($formAggregate->getIdForm());
    }

    public function __construct(
        int $idSection,
        array $innerData = [],
        array $config = []
    ) {
        $this->_idSection = $idSection;

        parent::__construct($innerData, $config);

        $this->setDataForm();
    }

    private function setDataForm()
    {
        if (!$this->search_type) {
            $this->search_type = SysVar::get('Search.default_type');
        }

        $this->getField('search_type')->type->default = $this->getTypeList();
        $this->getField('search_section')->type->default = $this->getSectionList();
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function save(): bool
    {
        return parent::save();
    }

    public function validate(string $formHash): bool
    {
        if (parent::validate($formHash)) {
            return true;
        }

        $this->getField('search_text')
            ->addError(
                'err_empty_field',
                \Yii::t('forms', 'err_empty_field')
            );

        return false;
    }

    public function getTypeList(): string
    {
        $aTypeList = [
            search\Type::allWords . ':' . \Yii::t('search', 'all_words'),
            search\Type::anyWord . ':' . \Yii::t('search', 'any_words'),
            search\Type::exact . ':' . \Yii::t('search', 'phrase_criteria'),
        ];

        return implode(';', $aTypeList);
    }

    public function getSectionList(): string
    {
        $iPolicyId = Auth::getPolicyId('public');
        $aSections = [\Yii::t('search', 'all_site')];

        foreach (Tree::getSectionList(
            \Yii::$app->sections->topMenu(),
            $iPolicyId
        ) as $aSection) {
            if (isset($aSection['visible']) && $aSection['visible'] > 0) {
                $aSections[$aSection['id']] = $aSection['id'] . ':' . $aSection['title'];
            }
        }

        foreach (Tree::getSectionList(
            \Yii::$app->sections->leftMenu(),
            $iPolicyId
        ) as $aSection) {
            if (isset($aSection['visible']) && $aSection['visible'] > 0) {
                $aSections[$aSection['id']] = $aSection['id'] . ':' . $aSection['title'];
            }
        }

        return implode(';', $aSections);
    }

    public function setAddParamsForShowForm(TemplateForm &$templateForm)
    {
        $templateForm->tagAction = "[{$this->_idSection}]";
    }
}
