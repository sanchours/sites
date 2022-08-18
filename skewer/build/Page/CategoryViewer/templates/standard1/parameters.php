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
//  [ 'groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'width_koef',       'paramTitle' => 'Множитель ширины',   'typeParam' => 'width_koef',    'defValue' => '3'],        // -> [block.width_koef]
//    [ 'groupTitle' => 'Блок', 'groupName' => 'block', 'paramName' => 'use_special_class',  'paramTitle' => 'Использовать спец.класс  (category__special)',   'typeParam' => 'yes/no',    'defValue' => 0],
];
