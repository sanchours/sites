<?php

namespace unit\base\orm;

use skewer\base\orm\Query;

/**
 * Тест на фасад.
 *
 * @covers \skewer\base\orm\Query
 */
class FacadeTest extends \Codeception\Test\Unit
{
    public function testQuery()
    {
        $mRes = Query::SQL('SHOW TABLES');
        $this->assertNotEmpty($mRes->fetchArray());
    }
}
