TablePrototype
==============

Класс [[skewer\build\Component\db\TablePrototype]] является прототипом для классов, реализующих отображение таблицы.

Класс сожержит следующие атрибуты:

- [[protected static $sTableName = '']]: Имя таблицы
- [[protected static $sKeyField = 'id']]: Имя ключевого поля
- [[protected static $aFieldList = array()]]: Описание полей таблицы

Класс содержит следующие методы

- [[public static function find( $id = null )]]: Поиск записей
- [[public static function getNewRow()]]: Получить экземлять записи для таблицы

Пример использования:

```php
class TableName extends TablePrototype {

    protected static $sTableName = 'table_name';

    protected static $aFieldList = array(
        'id' => array(),
        'name' => array(),
        'date' => array(),
        'a' => array(),
        'b' => array(),
        'c' => array()
    );

}
```

Пример поиска записей:

```php
$oRow = test_dir\TableName::find( 1 );
$aList = test_dir\TableName::find()->where( 'id<?', 3 )->getAll();
//---
$oQuery = test_dir\TableName::find()->where( 'id<?', 3 );
while( $row = $oQuery->each() )
    var_dump($row->id)
```