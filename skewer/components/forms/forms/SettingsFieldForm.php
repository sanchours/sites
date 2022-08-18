<?php

declare(strict_types=1);

namespace skewer\components\forms\forms;

use skewer\components\forms\components\LabelPosition;
use skewer\components\forms\components\protection\BlockJs;
use skewer\components\forms\components\protection\HiddenField;
use skewer\components\forms\components\WidthFactor;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class SettingsFieldForm
 * основные настройки форм, не имеющие специфической бизнес-логики.
 *
 * @property $labelPosition
 * @property $widthFactor
 * @property $slug
 */
class SettingsFieldForm extends InternalForm
{
    const LABEL_POSITION_LEFT = 'left';
    const LABEL_POSITION_RIGHT = 'right';
    const LABEL_POSITION_TOP = 'top';
    const LABEL_POSITION_NONE = 'none';

    public $title;
    public $required;
    public $description;
    public $newLine;
    public $groupPrevField;
    public $specStyle;
    public $classModify;

    private $_slug;
    private $_labelPosition = self::LABEL_POSITION_TOP;
    private $_widthFactor = 1;

    public function __construct(
        array $params,
        array $config = []
    ) {
        $this->setAttributes($params);

        $labelPosition = ArrayHelper::getValue($params, 'labelPosition');
        $this->setLabelPosition($labelPosition);

        $widthFactor = ArrayHelper::getValue($params, 'widthFactor');
        $this->setWidthFactor($widthFactor);

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['widthFactor'], 'integer'],
            [['required', 'newLine', 'groupPrevField'], 'boolean'],
            [['title', 'slug', 'specStyle', 'classModify'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['labelPosition'], 'string', 'max' => 5],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => \Yii::t('forms', 'param_title'),
            'slug' => \Yii::t('forms', 'param_name'),
        ];
    }

    public function getLabelPosition()
    {
        return $this->_labelPosition;
    }

    public function setLabelPosition(string $label = null)
    {
        if ($label !== null && LabelPosition::hasLabel($label)) {
            $this->_labelPosition = $label;
        } else {
            $this->_labelPosition = LabelPosition::getDefaultLabel();
        }
    }

    public function getWidthFactor(): int
    {
        return $this->_widthFactor;
    }

    public function setWidthFactor(int $factor = null)
    {
        if ($factor !== null && WidthFactor::hasFactor($factor)) {
            $this->_widthFactor = $factor;
        } else {
            $this->_widthFactor = WidthFactor::getDefaultFactor();
        }
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
        if (in_array($slug, [HiddenField::$nameHideField, BlockJs::$nameField])) {
            throw new UserException(
                \Yii::t('forms', 'field_identifier_forbidden_use')
            );
        }

        $this->_slug = $slug;
    }

    public function getShortNameObject(): string
    {
        return 'settings';
    }

    public static function getLocationsOfLabel(): array
    {
        return [
            self::LABEL_POSITION_LEFT => \Yii::t('forms', 'position_left'),
            self::LABEL_POSITION_TOP => \Yii::t('forms', 'position_top'),
            self::LABEL_POSITION_RIGHT => \Yii::t('forms', 'position_right'),
            self::LABEL_POSITION_NONE => \Yii::t('forms', 'position_none'),
        ];
    }

    public static function getFactorsOfWidth(): array
    {
        return [
            '1' => 'x1',
            '2' => 'x2',
            '3' => 'x3',
            '4' => 'x4',
        ];
    }
}
