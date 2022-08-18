<?php
/**
 * Тест на объект запись
 * User: ilya
 * Date: 24.03.14
 * Time: 16:11.
 */

namespace unit\base\orm;

use skewer\base\orm\Query;

class ActiveRecordTest extends \Codeception\Test\Unit
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

    protected function getRow()
    {
        $oRow = TableName::find(1);

        return $oRow;
    }

    /**
     * Удаление записи.
     *
     * @covers \skewer\base\orm\ActiveRecord::delete
     */
    public function testDelete()
    {
        $oRow = TableName::find(1);
        $res = $oRow->delete();

        $this->assertTrue($res, 'Неверный ответ при удалении записи');

        $oRow = TableName::find(1);

        $this->assertFalse($oRow, 'не удалилась запись');
    }

    /**
     * Попытка удаления несуществующей записи.
     *
     * @covers \skewer\base\orm\ActiveRecord::delete
     */
    public function testDeleteNonExsist()
    {
        $oRow = TableName::find(1);
        $oRow->id = 10;
        $res = $oRow->delete();

        $this->assertFalse($res, 'Неверный ответ');
    }

    /**
     * Сохранение новой записи.
     *
     * @covers \skewer\base\orm\ActiveRecord::save
     */
    public function testSaveNew()
    {
        $oRow = TableName::getNewRow();
        $oRow->name = 'newrow';
        $id = $oRow->save();

        $this->assertSame(5, $id, 'Неверный ответ функции');

        $oRow = TableName::find($id);

        $this->assertSame(5, $oRow->id, 'данные не сохранены');
        $this->assertSame('newrow', $oRow->name, 'данные не сохранены');
    }

    /**
     * обновление записи.
     *
     * @covers \skewer\base\orm\ActiveRecord::save
     */
    public function testSaveExsist()
    {
        $id = 3;
        $oRow = TableName::find($id);
        $oRow->name = 'newname';
        $res = $oRow->save();

        $this->assertSame($id, $res, 'Неверный ответ функции');
        $this->assertNotEmpty($oRow->save(), '0 при повторном сохранении');
        $this->assertSame($id, (int) $oRow->id, 'ошибка при сохранении');
        $this->assertSame('newname', $oRow->name, 'ошибка при сохранении');

        $oRow = TableName::find($id);
        $this->assertSame($id, (int) $oRow->id, 'данные не сохранены');
        $this->assertSame('newname', $oRow->name, 'данные не сохранены');
    }

    /**
     * обновление записи.
     *
     * @covers \skewer\base\orm\ActiveRecord
     */
    public function testSaveNewWithId()
    {
        $oRow = TableName::getNewRow();
        $oRow->id = 10;
        $oRow->name = 'newrow';
        $id = $oRow->save();

        $this->assertSame(10, $id, 'Неверный ответ функции');

        $oRow = TableName::find($id);

        $this->assertSame(10, (int) $oRow->id, 'данные не сохранены');
        $this->assertSame('newrow', $oRow->name, 'данные не сохранены');
    }
}
