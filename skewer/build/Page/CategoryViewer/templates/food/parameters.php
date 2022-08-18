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
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'width_koef',       'paramTitle' => 'Множитель ширины',   'typeParam' => 'width_koef',    'defValue' => '3'],  // -> [block.width_koef]
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'use_special_class',  'paramTitle' => 'Использовать спец.класс  (category__special)',   'typeParam' => 'yes/no',    'defValue' => 0],

  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'color',       'paramTitle' => 'Цвет',   'typeParam' => 'color',       'defValue' => '#000000'], // -> [header.color]
  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-family', 'paramTitle' => 'Шрифт',  'typeParam' => 'family',      'defValue' => 'Arial'],  // -> [header.font-family]
  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-size',   'paramTitle' => 'Размер', 'typeParam' => 'size',        'defValue' => '20px'],  // -> [header.font-size]
  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-style',  'paramTitle' => 'Курсив', 'typeParam' => 'font-style',  'defValue' => 'italic'], // -> [header.font-style]
];
