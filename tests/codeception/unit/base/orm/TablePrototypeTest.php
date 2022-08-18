<?php
/**
 * Тест на менеджер таблицы
 * User: ilya
 * Date: 24.03.14
 * Time: 16:11.
 */

namespace unit\base\orm;

use skewer\base\orm\Query;

class TablePrototypeTest extends \Codeception\Test\Unit
{
    protected function setUp()
    {
        require_once __DIR__ . '/TableName.php';

        TableName::rebuildTable();

        Query::SQL(
            "INSERT INTO `table_name` (`id`, `name`, `date`, `a`, `b`, `c`) VALUES
                        (1, 'first', '2014-03-12', 1, 1, 1),
                        (2, 'second', '2014-03-13', 1, 0, 0),
                        (3, 'third', '2014-03-19', 1, 1, 0),
                        (4, 'qqq', '2014-03-19', 0, 1, 1);"
        );
    }

    protected function tearDown()
    {
        Query::SQL('DROP TABLE `table_name`');
    }

    /**
     * Тест на поиск записи по id.
     *
     * @covers \skewer\base\orm\TablePrototype::find
     */
    public function testFindById()
    {
        $oRow = TableName::find(1);

        $this->assertInstanceOf('skewer\base\orm\ActiveRecord', $oRow, 'Неверный тип класса');

        $this->assertSame(1, $oRow->id);
        $this->assertSame('first', $oRow->name);
    }

    /**
     * Тест на поиск набора записей.
     *
     * @covers \skewer\base\orm\TablePrototype::find
     */
    public function testFindArr()
    {
        $aList = TableName::find()->where('id<?', 3)->getAll();

        $this->assertCount(2, $aList);
        $this->assertInstanceOf('skewer\base\orm\ActiveRecord', $aList[0], 'Неверный тип класса');
        $this->assertSame(1, $aList[0]->id);
        $this->assertSame('first', $aList[0]->name);
        $this->assertInstanceOf('skewer\base\orm\ActiveRecord', $aList[1], 'Неверный тип класса');
        $this->assertSame(2, $aList[1]->id);
        $this->assertSame('second', $aList[1]->name);
    }

    /**
     * Получение экземпляра Row.
     *
     * @covers \skewer\base\orm\TablePrototype::getNewRow
     */
    public function testGetNewRow()
    {
        $oRow = TableName::getNewRow();
        $this->assertInstanceOf('skewer\base\orm\ActiveRecord', $oRow, 'Неверный тип класса');
    }
}
