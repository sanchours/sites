<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\orm;

class SubscribeMessageRow extends orm\ActiveRecord
{
    public $id = 'NULL';
    public $title = '';
    public $text = '';
    public $template = 0;
    public $status = 0;

    public function __construct()
    {
        $this->setTableName('subscribe_msg');
        $this->setPrimaryKey('id');
    }
}
