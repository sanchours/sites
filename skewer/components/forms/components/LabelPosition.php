<?php

declare(strict_types=1);

namespace skewer\components\forms\components;

/**
 * Class LabelPosition
 * хранит основные функции по позиционированию заголовка.
 */
class LabelPosition
{
    const LABEL_POSITION_LEFT = 'left';
    const LABEL_POSITION_RIGHT = 'right';
    const LABEL_POSITION_TOP = 'top';
    const LABEL_POSITION_NONE = 'none';

    public static function hasLabel(string $label): bool
    {
        return in_array($label, self::getLabels());
    }

    public static function getDefaultLabel(): string
    {
        return self::LABEL_POSITION_TOP;
    }

    public static function getLabelsWithTitle(): array
    {
        $labelsWithTitle = [];

        foreach (self::getLabels() as $label) {
            $labelsWithTitle[$label] = \Yii::t('forms', "position_{$label}");
        }

        return $labelsWithTitle;
    }

    private static function getLabels(): array
    {
        return [
            self::LABEL_POSITION_LEFT,
            self::LABEL_POSITION_TOP,
            self::LABEL_POSITION_RIGHT,
            self::LABEL_POSITION_NONE,
        ];
    }
}
