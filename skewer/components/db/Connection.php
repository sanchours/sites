<?php

namespace skewer\components\db;

use yii\db\Exception;
use Yii;

/**
 * Class Connection for catching the database errors
 * @package skewer\components\db
 */
class Connection extends \yii\db\Connection
{
    /**
     * @throws \Exception
     */
    public function open()
    {
        try {
            return parent::open();
        } catch (Exception $e) {
            Yii::$app->getLog()->init();
            throw new \Exception('Error connecting to database: ' . $e->getMessage(), $e->getCode());
        }
    }

}
