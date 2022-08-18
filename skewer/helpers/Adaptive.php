<?php

namespace skewer\helpers;

use skewer\base\site\Layer;
use skewer\components\design\model\Params;
use yii\helpers\ArrayHelper;

/**
 * Класс помощник для адаптивного режима.
 */
class Adaptive
{
    const BREAKPOINT_DESKTOP_TOP = 'break_desktop_top';
    const BREAKPOINT_DESKTOP = 'break_desktop';
    const BREAKPOINT_DESKTOP_TABLE = 'break_tablet';
    const BREAKPOINT_MOBILE_DOWN = 'break_mobile_down';

    public static function modeIsActive()
    {
        static $bOn = null;
        if ($bOn === null) {
            $bOn = \Yii::$app->register->moduleExists('AdaptiveMode', Layer::PAGE);
        }

        return $bOn;
    }

    /**
     * Выдает текст переданной переменной только если включен адаптивный режим
     *
     * twig:
     *  * {{ Adaptive.write('') }}
     *  * {% if Adaptive.modeIsActive() %}{% endif %}
     *
     * php:
     *  * <?= Adaptive::write('') ?>
     *  * <?php if (Adaptive::modeIsActive()): ?><?php endif; ?>
     *
     * @param string $sText
     *
     * @return string
     */
    public static function write($sText)
    {
        return self::modeIsActive() ? $sText : '';
    }

    public static function getBreakpoints()
    {
        $breakpointsName = [
            'adaptive.' . self::BREAKPOINT_DESKTOP_TOP,
            'adaptive.' . self::BREAKPOINT_DESKTOP,
            'adaptive.' . self::BREAKPOINT_DESKTOP_TABLE,
            'adaptive.' . self::BREAKPOINT_MOBILE_DOWN,
        ];

        $breakpoints = Params::find()
            ->select(['name', 'value'])
            ->where(['name' => $breakpointsName])
            ->asArray()
            ->all();

        foreach ($breakpoints as &$breakpoint) {
            $breakpoint['name'] = str_replace('adaptive.', '', $breakpoint['name']);
            $breakpoint['value'] = (int) $breakpoint['value'];
        }

        $breakpoints = ArrayHelper::map($breakpoints, 'name', 'value');

        return $breakpoints;
    }
}
