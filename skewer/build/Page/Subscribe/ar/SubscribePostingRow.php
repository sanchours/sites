<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\orm;

class SubscribePostingRow extends orm\ActiveRecord
{
    public $id = 'NULL';
    public $list = '';
    public $state = '';
    public $post_date = '';
    public $last_pos = 0;
    public $id_body = 0;
    public $id_from = 0;

    public function __construct()
    {
        $this->setTableName('subscribe_posting');
        $this->setPrimaryKey('id');
    }
}
