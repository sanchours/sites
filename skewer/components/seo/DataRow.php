<?php

namespace skewer\components\seo;

use skewer\base\orm;

class DataRow extends orm\ActiveRecord
{
    public $id = 'NULL';
    public $group = '';
    public $row_id = 0;
    public $section_id = 0;
    public $title = '';
    public $keywords = '';

    /**
     * @var string Галлерея.
     * Используется для OpenGraph разметки
     */
    public $seo_gallery = '';
    public $description = '';
    public $frequency = '';
    public $priority = '';
    public $none_index = false;
    public $none_search = false;
    public $add_meta = '';

    public function __construct()
    {
        $this->setTableName('seo_data');
    }
}
