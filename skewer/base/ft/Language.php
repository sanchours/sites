<?php

namespace skewer\base\ft;

$aLanguage = [];

$aLanguage['ru']['field_type_int'] = 'число';
$aLanguage['ru']['field_type_float'] = 'дробное число';
$aLanguage['ru']['field_type_money'] = 'деньги';
$aLanguage['ru']['field_type_string'] = 'строка';
$aLanguage['ru']['field_type_text'] = 'текст';
$aLanguage['ru']['field_type_wyswyg'] = 'wyswyg-редактор';
$aLanguage['ru']['field_type_select'] = 'справочник';
$aLanguage['ru']['field_type_check'] = 'галочка';
$aLanguage['ru']['field_type_file'] = 'файл';
$aLanguage['ru']['field_type_gallery'] = 'галерея';
$aLanguage['ru']['field_type_date'] = 'дата';
$aLanguage['ru']['field_type_time'] = 'время';
$aLanguage['ru']['field_type_hide'] = 'скрытое поле';
$aLanguage['ru']['field_type_datetime'] = 'дата и время';
$aLanguage['ru']['field_type_dictionary'] = 'справочник';

$aLanguage['ru']['error_validator_set'] = 'Поле должно быть задано';
$aLanguage['ru']['error_validator_sys_name'] = 'Допустимы английские буквы, цифры и символ подчеркивания';
$aLanguage['ru']['error_validator_unique'] = 'Запись с таким значением уже существует';

/***********************************/

$aLanguage['en']['field_type_int'] = 'number';
$aLanguage['en']['field_type_float'] = 'float';
$aLanguage['en']['field_type_money'] = 'money';
$aLanguage['en']['field_type_string'] = 'string';
$aLanguage['en']['field_type_text'] = 'text';
$aLanguage['en']['field_type_wyswyg'] = 'wyswyg-editor';
$aLanguage['en']['field_type_select'] = 'dictionary';
$aLanguage['en']['field_type_check'] = 'checkbox';
$aLanguage['en']['field_type_file'] = 'file';
$aLanguage['en']['field_type_gallery'] = 'gallery';
$aLanguage['en']['field_type_date'] = 'date';
$aLanguage['en']['field_type_time'] = 'time';
$aLanguage['en']['field_type_hide'] = 'hidden';
$aLanguage['en']['field_type_datetime'] = 'date and time';
$aLanguage['en']['field_type_dictionary'] = 'dictionary';

$aLanguage['en']['error_validator_set'] = 'Field is required';
$aLanguage['en']['error_validator_sys_name'] = 'Must contain only letters, numbers and underscore char';
$aLanguage['en']['error_validator_unique'] = 'Row with such value already exists';

return $aLanguage;
