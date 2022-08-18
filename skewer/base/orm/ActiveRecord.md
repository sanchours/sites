ActiveRecord
============

Класс [[skewer\build\Component\db\ActiveRecord]] реализует механики для формирования и работы с записями по патерну ActiveRecord.

Класс содержит следующие методы

- [[public static function save()]]: Сохранение состояния записи
- [[public static function delete()]]: Удаление записи
- [[public function setData( $aData = array() )]]: Установка значения полей для записи
- [[public function getData()]]: Получение значений полей для записи в виде массива
- [[public static function init( $sTableName, $aFieldList, $aFieldDesc = array() )]]: создание объекта по формату
- [[public function compositePK()]]: true, если первичный ключ - составной
- [[public function fieldInPK()]]: Сообщает является ли заданное поле ключевым (в составном ключе или одиночном)
