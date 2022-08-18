<?php

namespace skewer\build\Page\Subscribe\ar;

use skewer\base\orm;

class SubscribeTemplateRow extends orm\ActiveRecord
{
    public $id = 'NULL';
    public $title = '';
    public $content = '';

    public function __construct()
    {
        $this->setTableName('subscribe_templates');
        $this->setPrimaryKey('id');
    }
}
