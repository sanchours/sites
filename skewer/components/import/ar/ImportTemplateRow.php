<?php

namespace skewer\components\import\ar;

use skewer\base\orm;

class ImportTemplateRow extends orm\ActiveRecord
{
    public $id = 0;
    public $title = '';
    public $card = '';
    public $coding = 'utf-8';
    public $type = 0;
    public $source = '';
    public $provider_type = '';
    public $settings = '';
    public $use_dict_cache = '';
    public $use_goods_hash = '';
    public $send_error = 1;
    public $clear_log = 1;
    public $send_notify = 1;

    public function __construct()
    {
        $this->setTableName('import_template');
        $this->setPrimaryKey('id');
    }
}
