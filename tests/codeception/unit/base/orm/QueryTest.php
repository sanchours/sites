<?php
/**
 * Тесты на запросник
 * User: ilya
 * Date: 19.03.14.
 */

namespace unit\base\orm;

use skewer\base\orm\Query;

/**
 * @covers \skewer\base\orm\Query
 * Class QueryTest
 */
class QueryTest extends \Codeception\Test\Unit
{
    protected $sTableName = 'table_name';

    protected function setUp()
    {
        Query::SQL(
            'CREATE TABLE IF NOT EXISTS `table_name` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `name` varchar(255) NOT NULL,
                      `date` date NOT NULL,
                      `a` int(11) NOT NULL,
                      `b` int(11) NOT NULL,
                      `c` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;'
        );

        Query::SQL(
            'CREATE TABLE IF NOT EXISTS `table_name2` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` varchar(255) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;'
        );

        Query::SQL(
            "INSERT INTO `table_name` (`id`, `name`, `date`, `a`, `b`, `c`) VALUES
                        (1, 'first', '2014-03-12', 1, 1, 1),
                        (2, 'second', '2014-03-13', 1, 0, 0),
                        (3, 'third', '2014-03-19', 1, 1, 0),
                        (4, 'qqq', '2014-03-19', 0, 1, 1);"
        );

        Query::SQL(
            "INSERT INTO `table_name2` (`id`, `title`) VALUES (1, 'T_first'), (2, 'T_second');"
        );
    }

    protected function tearDown()
    {
        Query::SQL('DROP TABLE `table_name`');
        Query::SQL('DROP TABLE `table_name2`');
    }

    /**
     * Именованные и неименованные переменные.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSQL()
    {
        $oResult = Query::SQL('SELECT * FROM `table_name` WHERE `name`=:name', ['name' => 'first']);

        $aRow = $oResult->fetchArray();

        $this->assertNotNull($aRow);
        $this->assertSame($aRow['name'], 'first');

        $oResult = Query::SQL('SELECT * FROM `table_name` WHERE `name` = ?', 'first');

        $aRow = $oResult->fetchArray();

        $this->assertNotNull($aRow);
        $this->assertSame($aRow['name'], 'first');

        $oResult = Query::SQL('SELECT * FROM `table_name` WHERE `a` = ? AND `c` = ?', 0, 1);

        $aRow = $oResult->fetchArray();

        $this->assertNotNull($aRow);
        $this->assertSame($aRow['name'], 'qqq');
    }

    /**
     * Тест создания запроса черези инстанс класса.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testQueryConstruction()
    {
        $oQuery = new Query();
        $aItems = $oQuery->select()->from($this->sTableName)->asArray()->getAll();
        $this->assertSame(count($aItems), 4);
        $this->assertSame($aItems[0]['name'], 'first');
    }

    /**
     * Тест на корректное формирование запроса SELECT.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectGetQuery()
    {
        $sQuery = Query::SelectFrom('table_name')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name`');
    }

    /**
     * Тест на корректное формирование запроса SELECT WHERE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectWhere()
    {
        $sQuery = Query::SelectFrom('table_name')->where('a', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=1');

        $sQuery = Query::SelectFrom('table_name')->where('name', 'second')->getQuery();
        $this->assertSame($sQuery, "SELECT * FROM `table_name` WHERE `name`='second'");

        $sQuery = Query::SelectFrom('table_name')->where('a', 1)->where('b', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=1 AND `b`=1');

        $sQuery = Query::SelectFrom('table_name')->where('a', 1)->orWhere('b', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=1 OR `b`=1');
    }

    /**
     * Тест на корректное формирование запроса SELECT WHERE с выражениями в виде условия.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectWhereExpressions()
    {
        $sQuery = Query::SelectFrom('table_name')->where('a=?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=1');

        $sQuery = Query::SelectFrom('table_name')->where('a = ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=1');

        $sQuery = Query::SelectFrom('table_name')->where('a>?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`>1');

        $sQuery = Query::SelectFrom('table_name')->where('a > ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`>1');

        $sQuery = Query::SelectFrom('table_name')->where('a>=?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`>=1');

        $sQuery = Query::SelectFrom('table_name')->where('a >= ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`>=1');

        $sQuery = Query::SelectFrom('table_name')->where('a<?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`<1');

        $sQuery = Query::SelectFrom('table_name')->where('a < ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`<1');

        $sQuery = Query::SelectFrom('table_name')->where('a<=?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`<=1');

        $sQuery = Query::SelectFrom('table_name')->where('a <= ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`<=1');

        $sQuery = Query::SelectFrom('table_name')->where('a!=?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`!=1');

        $sQuery = Query::SelectFrom('table_name')->where('a != ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`!=1');

        $sQuery = Query::SelectFrom('table_name')->where('a<>?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`<>1');

        $sQuery = Query::SelectFrom('table_name')->where('a <> ?', 1)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`<>1');

        $sQuery = Query::SelectFrom('table_name')->where('a', [1, 2, 3])->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a` IN (1,2,3)');

        $sQuery = Query::SelectFrom('table_name')->where('a NOT IN ?', [1, 2, 3])->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a` NOT IN (1, 2, 3)');

        $sQuery = Query::SelectFrom('table_name')->where('a IN ?', ['a', 'b', 'c'])->getQuery();
        $this->assertSame($sQuery, "SELECT * FROM `table_name` WHERE `a` IN ('a', 'b', 'c')");

        $sQuery = Query::SelectFrom('table_name')->where('a=b')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=`b`');

        $sQuery = Query::SelectFrom('table_name')->where('a = b')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`=`b`');

        $sQuery = Query::SelectFrom('table_name')->where('a>=b')->where('c')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`>=`b` AND `c`=1');

        $sQuery = Query::SelectFrom('table_name')->where('a >= b')->where('c')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` WHERE `a`>=`b` AND `c`=1');

        $sQuery = Query::SelectFrom('table_name')->where('date BETWEEN ?', ['2014-03-13 00:00:00', '2014-03-13 23:59:59'])->getQuery();
        $this->assertSame($sQuery, "SELECT * FROM `table_name` WHERE (`date` BETWEEN '2014-03-13 00:00:00' AND '2014-03-13 23:59:59')");
    }

    /**
     * Тест на корректное формирование запроса SELECT ORDER BY.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectOrder()
    {
        $sQuery = Query::SelectFrom('table_name')->order('a')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` ORDER BY `a` ASC');

        $sQuery = Query::SelectFrom('table_name')->order('a', 'DESC')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` ORDER BY `a` DESC');

        $sQuery = Query::SelectFrom('table_name')->order('a')->order('b', 'DESC')->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` ORDER BY `a` ASC, `b` DESC');
    }

    /**
     * Тест на корректное формирование запроса SELECT LIMIT.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectLimit()
    {
        $sQuery = Query::SelectFrom('table_name')->limit(10)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` LIMIT 10');

        $sQuery = Query::SelectFrom('table_name')->limit(10, 2)->getQuery();
        $this->assertSame($sQuery, 'SELECT * FROM `table_name` LIMIT 2, 10');
    }

    /**
     * Тест на корректное формирование запроса SELECT с заданным набором полей.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectFields()
    {
        $sQuery = Query::SelectFrom('table_name')->fields('a,b,c')->getQuery();
        $this->assertSame($sQuery, 'SELECT `a`, `b`, `c` FROM `table_name`');

        $sQuery = Query::SelectFrom('table_name')->fields(['a', 'b', 'c'])->getQuery();
        $this->assertSame($sQuery, 'SELECT `a`, `b`, `c` FROM `table_name`');
    }

    public function testMultiSelect()
    {
        $sQuery = Query::SelectFrom('table_name,table_name2')->fields('id,name,title')->getQuery();
        $this->assertSame($sQuery, 'SELECT `id`, `name`, `title` FROM `table_name`, `table_name2`');

        $sQuery = Query::SelectFrom('table_name AS t1, table_name2 AS t2')->fields('id,name,title')->getQuery();
        $this->assertSame($sQuery, 'SELECT `id`, `name`, `title` FROM `table_name` AS t1, `table_name2` AS t2');

        $sQuery = Query::SelectFrom('table_name AS t1, table_name2 AS t2')
            ->fields('t1.id,t1.name,t2.title')
            ->where('t1.id=t2.id')
            ->getQuery();

        $this->assertSame(
            $sQuery,
            'SELECT t1.`id`, t1.`name`, t2.`title` FROM `table_name` AS t1, `table_name2` AS t2 WHERE t1.`id`=t2.`id`'
        );

        $oQuery = new Query();
        $sQuery = $oQuery->select()
            ->from('table_name', 't1')
            ->from('table_name2', 't2')
            ->fields('t1.id,t1.name,t2.title')
            ->where('t1.id=t2.id')
            ->getQuery();

        $this->assertSame(
            $sQuery,
            'SELECT t1.`id`, t1.`name`, t2.`title` FROM `table_name` AS t1, `table_name2` AS t2 WHERE t1.`id`=t2.`id`'
        );
    }

    /**
     * Тест на корректное формированиезарпоса SELECT JOIN.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectJoin()
    {
        $oQuery = new Query();
        $sQuery = $oQuery->select()
            ->from('table_name', 't1')
            ->fields('t1.id,name,title')
            ->join('left', 'table_name2', 'jt1', 'jt1.id=t1.id')
                //->on( 'jt1.id=t1.id' )
            ->getQuery();

        $this->assertSame(
            $sQuery,
            'SELECT t1.`id`, `name`, `title` FROM `table_name` AS t1 LEFT JOIN `table_name2` AS jt1 ON jt1.`id`=t1.`id`'
        );
    }

    /**
     * Тест на корректное формированиезарпоса SELECT JOIN ON.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectJoinOn()
    {
        $oQuery = new Query();
        $sQuery = $oQuery->select()
            ->from('table_name', 't1')
            ->fields('t1.id,name,title')
            ->join('left', 'table_name2', 'jt1', 'jt1.id=t1.id')
            ->on('jt1.id<?', 2)
            ->getQuery();

        $this->assertSame(
            $sQuery,
            'SELECT t1.`id`, `name`, `title` FROM `table_name` AS t1 LEFT JOIN `table_name2` AS jt1 ON jt1.`id`=t1.`id` AND jt1.`id`<2'
        );
    }

    /**
     * Тест на корректное формирование результатов зарпоса SELECT.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectGetResult()
    {
        $aItems = Query::SelectFrom('table_name')->asArray()->getAll();
        $this->assertSame(count($aItems), 4);

        $aItem = Query::SelectFrom('table_name')->asArray()->getOne();
        $this->assertSame(count($aItem), 6);
        $this->assertSame($aItem['id'], '1');
        $this->assertSame($aItem['name'], 'first');

        $aItem = Query::SelectFrom('table_name')->where('a>?', 0)->asArray()->getAll();
        $this->assertSame(count($aItem), 3);

        $aItem = Query::SelectFrom('table_name')->where('a<=b')->where('c')->asArray()->getAll();
        $this->assertSame(count($aItem), 2);

        $aItems = Query::SelectFrom('table_name')->where('c>=?', 0)->order('name')->limit(2, 1)->asArray()->getAll();
        $this->assertSame(count($aItems), 2);
        $this->assertSame($aItems[0]['name'], 'qqq');
        $this->assertSame($aItems[1]['name'], 'second');
    }

    /**
     * Тест на корректное формирование результатов зарпоса SELECT из нескольких таблиц.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testMultiSelectGetResult()
    {
        $aItems = Query::SelectFrom('table_name AS t1, table_name2 AS t2')
            ->fields('t1.id, t1.name, t2.title')
            ->where('t1.id=t2.id')
            ->getAll();

        $this->assertCount(2, $aItems, 'эневерное кол-во записей');
        $this->assertCount(3, $aItems[0], 'эневерное кол-во полей');
        $this->assertSame('1', $aItems[0]['id'], 'неверное значение');
        $this->assertSame('first', $aItems[0]['name'], 'неверное значение');
        $this->assertSame('T_first', $aItems[0]['title'], 'неверное значение');
    }

    /**
     * Тест на корректное формирование результатов зарпоса SELECT с JOIN.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSelectJoinGetResult()
    {
        $oQuery = new Query();
        $aItems = $oQuery->select()
            ->from('table_name', 't1')
            ->fields('t1.id,name,title')
            ->join('left', 'table_name2', 'jt1', 'jt1.id=t1.id')
            ->getAll();

        $this->assertCount(4, $aItems, 'неверное кол-во записей');
        $this->assertCount(3, $aItems[0], 'неверное кол-во полей');
        $this->assertSame('1', $aItems[0]['id'], 'неверное значение');
        $this->assertSame('first', $aItems[0]['name'], 'неверное значение');
        $this->assertSame('T_first', $aItems[0]['title'], 'неверное значение');
        $this->assertSame('4', $aItems[3]['id'], 'неверное значение');
        $this->assertSame('qqq', $aItems[3]['name'], 'неверное значение');
        $this->assertNull($aItems[3]['title'], 'неверное значение');
    }

    /**
     * Тест на корректное формирование запроса UPDATE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testUpdateSimple()
    {
        $sQuery = Query::UpdateFrom('table_name')->set('a', 1)->where('id', 1)->getQuery();
        $this->assertSame($sQuery, 'UPDATE `table_name` SET `a`=1 WHERE `id`=1');

        $sQuery = Query::UpdateFrom('table_name')->set('a', 1)->set('b', 0)->where('id', 1)->getQuery();
        $this->assertSame($sQuery, 'UPDATE `table_name` SET `a`=1, `b`=0 WHERE `id`=1');
    }

    /**
     * Тест на корректное формирование запроса UPDATE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testUpdateExt()
    {
        $sQuery = Query::UpdateFrom('table_name')->set('a=b')->where('id', 1)->getQuery();
        $this->assertSame($sQuery, 'UPDATE `table_name` SET `a`=`b` WHERE `id`=1');
    }

    /**
     * Тест на корректное формирование запроса UPDATE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testUpdateMathExt()
    {
        $sQuery = Query::UpdateFrom('table_name')->set('a=b+?', 2)->where('id', 1)->getQuery();
        $this->assertSame($sQuery, 'UPDATE `table_name` SET `a`=`b`+2 WHERE `id`=1');
    }

    /**
     * Тест на корректное формирование запроса UPDATE с выборкой.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testUpdateWithSelection()
    {
        $sQuery = Query::UpdateFrom('table_name')
            ->set('a', 1)
            ->where('b', 1)->order('name')->limit(1)->getQuery();

        $this->assertSame($sQuery, 'UPDATE `table_name` SET `a`=1 WHERE `b`=1 ORDER BY `name` ASC LIMIT 1');
    }

    /**
     * Тест на корректное формирование результатов зарпоса UPDATE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testUpdateGetResults()
    {
        // изменение одного поля
        $sNewName = 'renamed';
        $iCount = Query::UpdateFrom('table_name')->set('name', $sNewName)->where('id', 1)->get();
        $this->assertSame($iCount, 1, 'Некорректный ответ');

        $aItems = Query::SelectFrom('table_name')->where('id', 1)->asArray()->getOne();
        $this->assertSame($aItems['id'], '1', 'Неверно выбрана запись');
        $this->assertSame($aItems['name'], $sNewName, 'Не сохранено значение');

        // изменение нескольких полей
        $iCount = Query::UpdateFrom('table_name')->set('c', 1)->set('b', 0)->where('id', 1)->get();
        $this->assertSame($iCount, 1, 'Некорректный ответ при обновлении нескольких полей');
        $aItems = Query::SelectFrom('table_name')->where('id', 1)->asArray()->getOne();
        $this->assertSame($aItems['id'], '1', 'Неверно выбрана запись');
        $this->assertSame($aItems['b'], '0', 'Не сохранено значение');
        $this->assertSame($aItems['c'], '1', 'Не сохранено значение');

        // изменение присвоением
        $iCount = Query::UpdateFrom('table_name')->set('a=b')->where('id', 1)->get();
        $this->assertSame($iCount, 1, 'Некорректный ответ');

        $aItems = Query::SelectFrom('table_name')->where('id', 1)->asArray()->getOne();
        $this->assertSame($aItems['id'], '1', 'Неверно выбрана запись');
        $this->assertSame($aItems['a'], '0', 'Не сохранено значение');
        $this->assertSame($aItems['b'], '0', 'Не сохранено значение');
        $this->assertSame($aItems['c'], '1', 'Не сохранено значение');
    }

    /**
     * Тест на корректное формирование запроса DELETE с выборкой.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testDeleteSimple()
    {
        $sQuery = Query::DeleteFrom('table_name')->where('a', 1)->getQuery();
        $this->assertSame($sQuery, 'DELETE  FROM `table_name` WHERE `a`=1');
    }

    /**
     * Тест на корректное формирование запроса DELETE с выборкой.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testDeleteWithSelection()
    {
        $sQuery = Query::DeleteFrom('table_name')->where('a', 0)->order('name')->limit(1)->getQuery();
        $this->assertSame($sQuery, 'DELETE  FROM `table_name` WHERE `a`=0 ORDER BY `name` ASC LIMIT 1');
    }

    /**
     * Тест на корректное формирование результатов зарпоса DELETE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testDeleteGetResults()
    {
        $iCount = Query::DeleteFrom('table_name')->where('id', 1)->get();
        $this->assertSame($iCount, 1);

        $aItems = Query::SelectFrom('table_name')->asArray()->getAll();
        $this->assertSame(count($aItems), 3);

        $iCount = Query::DeleteFrom('table_name')->where('a>=b')->get();
        $this->assertSame($iCount, 2);

        $aItems = Query::SelectFrom('table_name')->asArray()->getAll();
        $this->assertSame(count($aItems), 1);
    }

    /**
     * Тест на корректное формирование запроса INSERT с выборкой.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testInsertWithSet()
    {
        $sQuery = Query::InsertInto('table_name')->set('a', 1)->getQuery();
        $this->assertSame($sQuery, 'INSERT INTO `table_name` SET `a`=1');
    }

    /**
     * Тест на корректное формирование запроса INSERT с ON DUPLICATE KEY UPDATE.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testInsertWithDuplicate()
    {
        $sQuery = Query::InsertInto('table_name')
            ->set('id', 2)
            ->set('name', 'updname')
            ->onDuplicateKeyUpdate()
            ->set('name', 'updname')
            ->getQuery();

        $this->assertSame(
            $sQuery,
            "INSERT INTO `table_name` SET `id`=2, `name`='updname' ON DUPLICATE KEY UPDATE `name`='updname'"
        );
    }

    /**
     * Тест на корректное формирование результатов зарпоса INSERT.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testInsertGetResults()
    {
        $iCount = Query::InsertInto('table_name')->set('a', 1)->get();
        $this->assertSame($iCount, 5);

        $aItem = Query::SelectFrom('table_name')->where('id', 5)->asArray()->getOne();
        $this->assertSame($aItem['id'], '5', 'Неверно выбрана запись');
        $this->assertSame($aItem['b'], '0', 'Не сохранено значение');
        $this->assertSame($aItem['name'], '', 'Не сохранено значение');

        $iCount = Query::InsertInto('table_name')
            ->set('name', 'new_item')->set('a', 1)->set('c', 1)
            ->get();
        $this->assertSame($iCount, 6);

        $aItem = Query::SelectFrom('table_name')->where('id', 6)->asArray()->getOne();
        $this->assertSame($aItem['id'], '6', 'Неверно выбрана запись');
        $this->assertSame($aItem['a'], '1', 'Не сохранено значение');
        $this->assertSame($aItem['b'], '0', 'Не сохранено значение');
        $this->assertSame($aItem['c'], '1', 'Не сохранено значение');
        $this->assertSame($aItem['name'], 'new_item', 'Не сохранено значение');
    }

    /**
     * Тест на корректное формирование запроса INSERT с выборкой.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testInsertKeyDuplicate()
    {
        $iCount = Query::InsertInto('table_name')
            ->set('id', 2)
            ->set('name', 'updname')
            ->onDuplicateKeyUpdate()
            ->set('name', 'updname')
            ->get();

        $this->assertSame($iCount, 2, 'неверный результат');

        $aItems = Query::SelectFrom('table_name')->asArray()->getAll();
        $this->assertCount(4, $aItems, 'неверное кол-во записей');

        $aItem = Query::SelectFrom('table_name')->where('id', 2)->asArray()->getOne();
        $this->assertSame($aItem['id'], '2');
        $this->assertSame($aItem['name'], 'updname');
    }

    /**
     * Тестирование корректности формирования исходного SQL кода.
     *
     * @covers \skewer\base\orm\Query
     */
    public function testSourseSQLBuild()
    {
        $sQuery = Query::SelectFrom('users')
            ->where('field1', 1)
            ->where('field2', 2)
            ->where('field3', 3)
            ->where('field4', 4)
            ->where('field5', 5)
            ->where('field6', 6)
            ->where('field7', 7)
            ->where('field8', 8)
            ->where('field9', 9)
            ->where('field10', 10)
            ->where('field11', 'str')
            ->getQuery();

        $this->assertNotEmpty(mb_strpos($sQuery, 'str'));
    }
}
