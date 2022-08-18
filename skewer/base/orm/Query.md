Query
=====

Класс [[skewer\build\Component\db\Query]] реализует функционал для построения и выполнения SQL запросов.
Поддерживает вожножность статического и динамического вызова. Набор методов, для начала формирования запроса:

- [[db\Query::SelectFrom( 'table_name' )]]: Статический вызов построителя запросов на выборку данных
- [[Query->select()]]: динамический вызов построителя запросов на выборку данных
- [[db\Query::UpdateFrom( 'table_name' )]]: Статический вызов построителя запросов на обновление данных
- [[Query->update()]]: динамический вызов построителя запросов на обновление данных
- [[db\Query::DeleteFrom( 'table_name' )]]: Статический вызов построителя запросов на удаление данных
- [[Query->delete()]]: динамический вызов построителя запросов на удаление данных
- [[db\Query::InsertInto( 'table_name' )]]: Статический вызов построителя запросов на добавление данных
- [[Query->insert()]]: динамический вызов построителя запросов на добавление данных


 Примеры вызова:

```php
$aItems = Query::SelectFrom( 'table_name' )->where( 'id', 1 )->asArray()->getAll();
$oQuery = new Query();
$aItems = $oQuery->select()->from( 'table_name' )->where( 'id', 1 )->asArray()->getAll();
```

### `SELECT`

 Построения запросов на выборку данных начинается вызовом статического метода `Query::SelectFrom( 'table_name' )`
 с указанием имени таблицы или динамически `$oQuery->select()`. При построении запроса поддерживаются следующие функции:

- [[from( $mTableName )]]: задает имя таблицы на выборку
- [[fields( $mFieldList )]]: задает набор полей, участвующий в выборке
- [[where( $sExpr, $mValue = true )|andWhere|orWhere]]: задает условие в секцию `WHERE`
- [[order( $sField, $sWay = 'ASC' )]]: задает условие сортировки в секцию `ORDER BY`
- [[limit( $iCount, $iShift = 0 )]]: задает условие ограничение результаров выборки секции `LIMIT`
- [[join( $sType, $mTable, $sAlias = '', $sCondition )]]: выполнение операции `JOIN`
- [[on( $sExpr, $mValue = true )]]: добавление операции в секцию `ON` для последнего `JOIN`
- [[asArray()]]: устанавливает флаг, чтобы результат был отдан в виде массива
- [[getAll()]]: выполняет запрос и отдает результат в виде списка записей
- [[getOne()]]: выполняет запрос и отдает результат в виде первой найденной записи
- [[getQuery()]]: получить результирующую строку запроса в SQL формате

 Пример использования:

```php
$aItems = Query::SelectFrom( 'table_name' )->where( 'id', 1 )->asArray()->getAll();
$oQuery = new Query();
$aItems = $oQuery->select()->from( 'table_name' )->where( 'id', 1 )->asArray()->getAll();
```

 Пример мульти табличных выборок

```php
$aItems = Query::SelectFrom( 'table_name AS t1,table_name2 AS t2' )
            ->fields( 't1.id,t1.name,t2.title' )
            ->where( 't1.id=t2.id' )
            ->getAll();
// or
$oQuery = new Query();
$aItems = $oQuery->select()
    ->from( 'table_name',  't1' )
    ->from( 'table_name2', 't2' )
    ->fields( 't1.id,t1.name,t2.title' )
    ->where( 't1.id=t2.id' )
    ->getAll();
```

```sql
SELECT t1.`id`, t1.`name`, t2.`title` FROM `table_name` AS t1, `table_name2` AS t2 WHERE t1.`id`=t2.`id`
```

### `UPDATE`

 Построения запросов на обновление данных начинается вызовом статического метода `Query::UpdateFrom( 'table_name' )`
 с указанием имени таблицы или динамически `$oQuery->update()`. При построении запроса поддерживаются следующие функции:

- [[from( $mTableName )]]: задает имя таблицы на выборку
- [[where( $sExpr, $mValue = true )|andWhere|orWhere]]: задает условие в секцию `WHERE`
- [[order( $sField, $sWay = 'ASC' )]]: задает условие сортировки в секцию `ORDER BY`
- [[limit( $iCount, $iShift = 0 )]]: задает условие ограничение результаров выборки секции `LIMIT`
- [[get()]]: выполняет запрос и отдает результат в виде кол-ва обновленных записай
- [[getQuery()]]: получить результирующую строку запроса в SQL формате

 Пример использования:

```php
$aItems = Query::UpdateFrom( 'table_name' )->where( 'id', 1 )->set( 'a', 1 )->get();
$oQuery = new Query();
$aItems = $oQuery->update()->from( 'table_name' )->where( 'id', 1 )->set( 'a', 1 )->get();
```

### `INSERT`

 Построения запросов на добавление данных начинается вызовом статического метода `Query::InsertInto( 'table_name' )`
 с указанием имени таблицы или динамически `$oQuery->insert()`. При построении запроса поддерживаются следующие функции:

- [[table( $mTableName )]]: задает имя таблицы
- [[set( $sField, $sValue=null )]]: задает имя поле и его значение
- [[onDuplicateKeyUpdate()]]: задает секцию обновления, при совпадении ключей
- [[get()]]: выполняет запрос и отдает результат в виде идентификатора созданной записи
- [[getQuery()]]: получить результирующую строку запроса в SQL формате

 Пример использования:

```php
$aItems = Query::InsertInto( 'table_name' )->set( 'a', 1 )->get();
$oQuery = new Query();
$aItems = $oQuery->insert()->table( 'table_name' )->set( 'a', 1 )->get();
Query::InsertInto( 'table_name' )->set( 'id', 2 )->set( 'name', 'updname' )
->onDuplicateKeyUpdate()->set( 'name', 'updname' )->get();
```

### `DELETE`

 Построения запросов на удаление данных начинается вызовом статического метода `Query::DeleteFrom( 'table_name' )`
 с указанием имени таблицы или динамически `$oQuery->delete()`. При построении запроса поддерживаются следующие функции:

- [[from( $mTableName )]]: задает имя таблицы на выборку
- [[where( $sExpr, $mValue = true )|andWhere|orWhere]]: задает условие в секцию `WHERE`
- [[order( $sField, $sWay = 'ASC' )]]: задает условие сортировки в секцию `ORDER BY`
- [[limit( $iCount, $iShift = 0 )]]: задает условие ограничение результаров выборки секции `LIMIT`
- [[get()]]: выполняет запрос и отдает результат в виде кол-ва обновленных записай
- [[getQuery()]]: получить результирующую строку запроса в SQL формате

 Пример использования:

```php
$aItems = Query::DeleteFrom( 'table_name' )->where( 'id', 1 )->get();
$oQuery = new Query();
$aItems = $oQuery->delete()->from( 'table_name' )->where( 'id', 1 ))->get();
```


### `WHERE`

 Секция `WHERE` используется для запросов типа `SELECT`, `UPDATE`, `DELETE` и реалиуется функциями:

- [[where( $sExpr, $mValue = true )]]: задает условие через оператор `AND`
- [[andWhere( $sExpr, $mValue = true )]]: задает условие через оператор `AND`
- [[orWhere( $sExpr, $mValue = true )]]: задает условие через оператор `OR`

 Пример использования:

```php
$oItem = Query::SelectFrom( 'table_name' )->where( 'a', 1 )->andWhere( 'b', 1 )->orWhere( 'a', '0' )->getAll();
```

```sql
SELECT * FROM `table_name` WHERE a=1 and ( b=1 or c=1 ) or a=0 and c=0
```

Функции сожержат два входных параметра, где первый является обязательный и содержит выражение или имя поля,
а второй необязательный - данные. По умолчанию второй параметр интерпритируется как булевое `TRUE`.
Выражение может содержать один из допустимых операторов: `=`, `>=`, `<=`, `>`, `<`, `<>`, ` IN `, ` NOT IN `, ` BETWEEN `.
При этом правая и левая часть выражения интерпритируются как имена полей, однако если одно из них равно `?`,
оно заменяется на значение второго параметра функции (т.е. подставляются данные).
Если выражение не содержит ни одной из заданных операций, оно интерпритируется как имя поля и через оператор `=`
к нему приписывается второй параметр - значение.
Если второй параметр, переданный функции, является массивом, и не найден оператор, то устанавливается оператор `IN`, а
все значения, переданые в массиве приводятся к типу `int`

Пример использования

```php
// WHERE `a`=1
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'a' )->getAll();
// WHERE `a`=2
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'a', 2 )->getAll();
// WHERE `a`>=1
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'a>=?', 1 )->getAll();
// WHERE `name`='cur_name'
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'name', 'cur_name' )->getAll();
// WHERE `id` IN (1,2,3)
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'id', array(1,2,3) )->getAll();
// WHERE `a`>`b`
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'a>b' )->getAll();
// WHERE `a` NOT IN (1, 2, 3)
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'a NOT IN ?', array(1,2,3) )->getAll();
// WHERE `a` IN ('a', 'b', 'c')
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'a IN ?', array('a','b','c') )->getAll();
// WHERE (`date` BETWEEN '2014-03-13 00:00:00' AND '2014-03-13 23:59:59')
$oItem = Query::SelectFrom( 'table_name' )-> ... ->where( 'date BETWEEN ?', array( '2014-03-13 00:00:00', '2014-03-13 23:59:59' ) )->getAll();
```


### `SET`

 Секция `SET` используется для запросов типа `UPDATE` и поддерживает ряд математических операций операций:

```php
Query::UpdateFrom( 'table_name' )->set( 'a=b+?', 2 )->where( 'id', 1 )->getQuery();
```

```sql
UPDATE `table_name` SET `a`=`b`+2 WHERE `id`=1
```

### `ORDER BY`

 Секция `ORDER BY` используется для запросов типа `SELECT`, `UPDATE`, `DELETE` и реалиуется функцией:

- [[order( $sField, $sWay = 'ASC' )]]: добавляет в запрос поле `$sField` для сотрировки по типу `$sWay` (по умолчанию `ASC`)

 Пример использования:

```php
$oItem = Query::SelectFrom( 'table_name' )->order( 'a' )->order( 'b', 'DESC' )->getQuery()
```

```sql
SELECT * FROM `table_name` ORDER BY `a` ASC, `b` DESC
```


### `LIMIT`

 Секция `LIMIT` используется для запросов типа `SELECT`, `UPDATE`, `DELETE` и реалиуется функцией:

- [[limit( $iCount, $iShift = 0 )]]: обрабатывает `$iCount` записей, начиная с `$iShift`-ой

 Пример использования:

```php
$sQuery = Query::SelectFrom( 'table_name' )->limit( 10 )->getQuery();
$sQuery = Query::SelectFrom( 'table_name' )->limit( 10, 2 )->getQuery();
```

```sql
SELECT * FROM `table_name` LIMIT 10
SELECT * FROM `table_name` LIMIT 2, 10
```

### `JOIN`

 Секция `JOIN` используется для запросов типа `SELECT` и реалиуется функцией [[join( $sType, $mTable, $sAlias = '', $sCondition )]]

- [[$sType]]: Тип запроса; может принимать значения `left`, `right`, `inner`, `full outer`
- [[$mTable]]: Имя таблицы для операции
- [[$sAlias]]: Псевдоним для таблицы. Если не задан, то имеет значение `jt$i`, где `$i` - номер запроса
- [[$sCondition]]: Условие на склейку. Обрабатывается аналогично секции `WHERE`

 Пример использования:

```php
$oQuery = new Query();
$sQuery = $oQuery->select()
    ->from( 'table_name',  't1' )
    ->fields( 't1.id,name,title' )
    ->join( 'left', 'table_name2', 'jt1', 'jt1.id=t1.id' )
        ->on( 'jt1.id<?', 2 )
    ->getQuery();
```

```sql
SELECT t1.`id`, `name`, `title` FROM `table_name` AS t1 LEFT JOIN `table_name2` AS jt1 ON jt1.`id`=t1.`id` AND jt1.`id`<2
```

### TODO

- GROUP with sum, max, min, average
- HAVING
- Left, Right ... joins single function
- distinct and get real count
- class exception
- обработка ошибок
- константы на статические значения-
- операции сложения и тп