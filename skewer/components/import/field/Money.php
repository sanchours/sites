<?php

namespace skewer\components\import\field;

/**
 * Обработчик поля типа деньги.
 */
class Money extends Prototype
{
    /**
     * @var int - наценка
     */
    protected $markup = 0;

    /**
     * @var array - параметры для вывода на странице настроек полей
     */
    protected static $parameters = [
        'markup' => [
            'title' => 'markup',
            'datatype' => 'u',
            'viewtype' => 'float',
            'default' => 1,
        ],
    ];

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        $fSum = 0;

        foreach ($this->values as $value) {
            $fSum += self::parseToFloat($value);
        }
        $markup = $this->markup; // коэффициент наценки

        if ($markup > 0) {
            $fSum = $fSum * $markup;
        }

        // округляем в меньшую сторону как и в поле money в админ.интерфейсе
        $fRes = round($fSum, 2, PHP_ROUND_HALF_DOWN);

        return $fRes;
    }

    /**
     * Преобразует строку во float значение.
     *
     * @param $sValue
     *
     * @return float
     */
    protected function parseToFloat($sValue)
    {
        $sValue = preg_replace('/[^0-9,\.]/', '', $sValue);
        $sValue = rtrim($sValue, '.');

        $iPosDot = mb_strpos($sValue, '.'); // Позиция точки
        $iPosComma = mb_strpos($sValue, ','); // Позиция запятой

        if ($iPosDot && $iPosComma) {  // 2 различных разделителя
            //
            $sThousandDelimiter = $sValue[min($iPosDot, $iPosComma)];
            $sValue = str_replace($sThousandDelimiter, '', $sValue);
        } elseif ($iPosDot or $iPosComma) { // 1 разделитель
            $iPosDelimiter = $iPosDot ? $iPosDot : $iPosComma;
            $delimiter = $sValue[$iPosDelimiter];
            if (mb_substr_count($sValue, $delimiter) > 1) {
                $sValue = str_replace($delimiter, '', $sValue);
            }
        }

        $sValue = str_replace(',', '.', $sValue);

        return (float) $sValue;
    }
}
