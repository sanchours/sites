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
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'width_koef',       'paramTitle' => 'Множитель ширины',   'typeParam' => 'width_koef',    'defValue' => '4'],        // -> [block.width_koef]
  ['groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'use_special_class',  'paramTitle' => 'Использовать спец.класс  (category__special)',   'typeParam' => 'yes/no',    'defValue' => 0],

  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'color',       'paramTitle' => 'Цвет',   'typeParam' => 'color',       'defValue' => '#222'],   // -> [header.color]
  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-family', 'paramTitle' => 'Шрифт',  'typeParam' => 'family',      'defValue' => 'Arial'],  // -> [header.font-family]
  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-size',   'paramTitle' => 'Размер', 'typeParam' => 'size',        'defValue' => '24px'],   // -> [header.font-size]
  ['groupTitle' => 'Заголовок', 'groupName' => 'header', 'paramName' => 'font-weight', 'paramTitle' => 'Толщина шрифта', 'typeParam' => 'font-weight',  'defValue' => 'bold'], // -> [header.font-weight]

  ['groupTitle' => 'Кнопка', 'groupName' => 'button', 'paramName' => 'color',      'paramTitle' => 'Цвет текста',   'typeParam' => 'color',       'defValue' => '#222'],   // -> [button.color]
  ['groupTitle' => 'Кнопка', 'groupName' => 'button', 'paramName' => 'background', 'paramTitle' => 'Фон кнопки',    'typeParam' => 'color',      'defValue' => 'transparent'],  // -> [button.background]
  ['groupTitle' => 'Кнопка', 'groupName' => 'button', 'paramName' => 'background-border',   'paramTitle' => 'Цвет бордера', 'typeParam' => 'color',        'defValue' => '#222'],   // -> [button.background-border]

  ['groupTitle' => 'Кнопка', 'groupName' => 'button', 'paramName' => 'color-hover',      'paramTitle' => 'Цвет текста при наведение',   'typeParam' => 'color',       'defValue' => '#222'],   // -> [button.color-hover]
  ['groupTitle' => 'Кнопка', 'groupName' => 'button', 'paramName' => 'background-hover', 'paramTitle' => 'Фон кнопки при наведение',    'typeParam' => 'color',       'defValue' => 'transparent'],  // -> [button.background-hover]
  ['groupTitle' => 'Кнопка', 'groupName' => 'button', 'paramName' => 'background-border-hover',   'paramTitle' => 'Цвет бордера при наведение', 'typeParam' => 'color',        'defValue' => '#222'],   // -> [button.background-border-hover]

  ['groupTitle' => 'Текст', 'groupName' => 'text', 'paramName' => 'color',      'paramTitle' => 'Цвет текста',   'typeParam' => 'color',       'defValue' => '#222'],   // -> [text.color]
  ['groupTitle' => 'Текст', 'groupName' => 'text', 'paramName' => 'size', 'paramTitle' => 'Размер текста',    'typeParam' => 'size',       'defValue' => '16px'],  // -> [text.size]

  ['groupTitle' => 'Изображение', 'groupName' => 'img', 'paramName' => 'img-width',   'paramTitle' => 'Ширина изображения', 'typeParam' => 'size',        'defValue' => '100%'],   // -> [img.img-width]
];
