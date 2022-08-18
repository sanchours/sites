<?php

namespace skewer\components\import\ar;

use skewer\base\orm;

class LogRow extends orm\ActiveRecord
{
    public $id = 0;
    public $tpl = '';
    public $task = '';
    public $name = '';
    public $value = '';
    public $list = 0;
    public $saved = 0;

    /**
     * Флаг логировать ли обновление записи.
     *
     * @var bool
     */
    protected static $bLogUpdate = false;

    /**
     * Флаг логированть ли создание записи.
     *
     * @var bool
     */
    protected static $bLogCreate = false;

    /**
     * Флаг логировать ли удаление записи.
     *
     * @var bool
     */
    protected static $bLogDelete = false;

    public function __construct()
    {
        $this->setTableName('import_logs');
        $this->setPrimaryKey('id');
    }
}
