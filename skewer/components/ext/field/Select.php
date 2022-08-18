<?php

namespace skewer\components\ext\field;

/**
 * Редактор "Выпадающий список".
 */
class Select extends Prototype
{
    /** {@inheritdoc} */
    public function getView()
    {
        return 'select';
    }

    /** {@inheritdoc} */
    final public function setValue($mValue)
    {
        // Решение проблемы со строковой записью нуля, иначе не находит в списке индекс "0"
        if (is_numeric($mValue)) {
            $mValue = (int) $mValue;
        }

        return parent::setValue($mValue);
    }

    /** {@inheritdoc} */
    public function getDesc()
    {
        $aList = [];

        if ($this->getDescVal('emptyStr', true)) {
            $aList[] = [
                'v' => '',
                't' => '---',
            ];
        }

        // Сформировать варианты значений выпадающего списка из параметра show_val
        if ($mShowVal = $this->getDescVal('show_val')) {
            // Если в параметре указан путь к статическому методу откуда брать значения, то обработать его
            // Структура пути к статическому методу: \<полнуть путь к классу>::<имя метода>()
            if (!is_array($mShowVal) and preg_match('/^(\\\[\w\\\-]+::[\w\\\-]+)\(\)$/', $mShowVal, $aMatches)) {
                $mShowVal = is_callable($aMatches[1]) ? call_user_func($aMatches[1]) : '';
            }

            if (is_array($mShowVal)) {
                // Обработать массив вариантов значений
                foreach ($mShowVal as $key => $sVal) {
                    $aList[] = [
                        'v' => $key,
                        't' => \Yii::tSingleString($sVal),
                    ];
                }
            } else {
                // Обработать строку вариантов значений
                foreach (explode("\n", $mShowVal) as $sRow) {
                    if ($sRow = trim($sRow)) {
                        if (($iSepPos = mb_strpos($sRow, ':')) > 0) {
                            $sKey = rtrim(mb_substr($sRow, 0, $iSepPos));
                            $sVal = ltrim(mb_substr($sRow, $iSepPos + 1));
                        } else {
                            $sKey = $sVal = $sRow;
                        }

                        $aList[] = [
                            'v' => $sKey,
                            't' => \Yii::tSingleString($sVal),
                        ];
                    }
                }
            }
        }

        $this->setAddDesc([
            'valueField' => 'v',
            'displayField' => 't',
            'store' => [
                'fields' => ['v', 't'],
                'data' => $aList,
            ],
        ]);

        return parent::getDesc();
    }
}
