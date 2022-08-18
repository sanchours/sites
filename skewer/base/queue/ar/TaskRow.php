<?php

namespace skewer\base\queue\ar;

use skewer\base\log\Logger;
use skewer\base\orm\ActiveRecord;
use skewer\base\queue\Api;

class TaskRow extends ActiveRecord
{
    public $id = 0;
    public $global_id = 0;
    public $title = '';
    public $class = '';
    public $parameters = '';
    public $priority = 0;
    public $resource_use = 0;
    public $target_area = 0;
    public $upd_time = '';
    public $mutex = 0;
    public $status = 0;
    public $parent = 0;
    public $md5 = '';

    public function __construct()
    {
        $this->setTableName('task');
        $this->setPrimaryKey('id');
    }

    public function save()
    {
        $this->upd_time = date('Y-m-d H:i:s');

        $aStatuses = Api::getStatusList();

        $aData = $this->getData();

        if (isset($aStatuses[$aData['status']])) {
            $aData['status'] = $aStatuses[$aData['status']];
        }

        Logger::dumpTask($aData);

        return parent::save();
    }
}
