<?php
/**
 * groupTitle - название группы полей. Поля с одинаковым groupTitle будут сгруппированы
 * groupName  - техническое имя группы
 * paramName  - техническое имя параметра
 * paramTitle - название параметра
 * typeParam  - тип параметра(здесь используются теже типы что и в диз.режиме)
 * defValue   - значение по умолчанию.
 *
 * Обращение к параметру в шаблонах - [groupName.paramName]
 */

return [
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'width_koef',   'paramTitle' => 'Множитель ширины',   'typeParam' => 'width_koef',   'defValue' => '4'],        // -> [block.width_koef]
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'use_special_class',  'paramTitle' => 'Использовать спец.класс  (category__special)',   'typeParam' => 'yes/no',    'defValue' => 0],
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'blockColor',   'paramTitle' => 'Фон блока',   'typeParam' => 'color',   'defValue' => '#fafafc'],

  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'color',       'paramTitle' => 'Цвет',   'typeParam' => 'color',       'defValue' => '#222'], // -> [header.color]
    ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-family', 'paramTitle' => 'Шрифт',  'typeParam' => 'family',      'defValue' => 'Arial'],  // -> [header.font-family]
    ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-size',   'paramTitle' => 'Размер', 'typeParam' => 'size',        'defValue' => '16px'],  // -> [header.font-size]
    ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-style',  'paramTitle' => 'Курсив', 'typeParam' => 'font-style',  'defValue' => 'normal'], // -> [header.font-style]

  ['groupTitle' => 'Маркер', 'groupName' => 'marker', 'paramName' => 'color',       'paramTitle' => 'Цвет',   'typeParam' => 'color',       'defValue' => '#222'], // -> [header.color]
    ['groupTitle' => 'Маркер', 'groupName' => 'marker', 'paramName' => 'font-size',   'paramTitle' => 'Размер', 'typeParam' => 'size',        'defValue' => '22px'],  // -> [header.font-size]
    ['groupTitle' => 'Маркер', 'groupName' => 'marker', 'paramName' => 'right', 'paramTitle' => 'Отступ справа',  'typeParam' => 'size',      'defValue' => '0'],  // -> [header.font-family]
    ['groupTitle' => 'Маркер', 'groupName' => 'marker', 'paramName' => 'top',  'paramTitle' => 'Отступ сверху', 'typeParam' => 'size',  'defValue' => '-9px'], // -> [header.font-style]
];
