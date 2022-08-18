<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 25.06.13
 * Time: 15:40
 * To change this template use File | Settings | File Templates.
 */

namespace unit\base\orm\test_model;

use skewer\base\orm\ActiveRecord;

class TestArRow extends ActiveRecord
{
    public $id = 0;
    public $a = false;
    public $b = false;
    public $c = false;
    public $date = '';
    public $string = '';
    public $text = '';

    public function getTableName()
    {
        return 'test_ar';
    }

    public function insert()
    {
        return $this->save();
    }

    public function update()
    {
        return $this->save();
    }
}
