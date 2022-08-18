<?php

namespace skewer\base\log;

use yii\helpers\ArrayHelper;

class TaskFileTarget extends \yii\log\FileTarget
{
    public function formatMessage($message)
    {
        $aData = ArrayHelper::getValue($message, 0, '');
        $timestamp = ArrayHelper::getValue($message, 3, '');

        $text = sprintf(
            '%s - %s - [%d/%d] - params: {%s}',
            $aData['class'],
            $aData['status'],
            $aData['id'],
            $aData['global_id'],
            $aData['parameters']
        );

        return $this->getTime($timestamp) . ' ' . $text;
    }
}
