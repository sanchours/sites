<?php

namespace skewer\build\Tool\Import;

use skewer\base\ft\Exception;
use skewer\base\queue\ar\Schedule;
use skewer\base\site\Type;
use skewer\components\auth\models\GroupPolicy;
use skewer\components\auth\Policy;
use skewer\components\catalog\Attr;
use skewer\components\catalog\Card;
use skewer\components\config\InstallPrototype;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\Log;
use skewer\components\import\Task;

/**
 * Class Install.
 */
class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    // func

    public function install()
    {
        if (!Type::hasCatalogModule()) {
            $this->fail('Нельзя установить импорт на некаталожный сайт!');
        }

        Log::rebuildTable();
        ImportTemplate::rebuildTable();

        self::setTaskImport();

        $aPolicyAdmin = GroupPolicy::find()->where(['alias' => 'admin'])->asArray()->all();

        if ($aPolicyAdmin) {
            foreach ($aPolicyAdmin as $aPolicy) {
                Policy::addModule($aPolicy['id'], 'Import', \Yii::t('import', 'importAndUpdate'));
            }
        }

        //Добавим поле в базовую карточку для записи хэшей
        $aFieldData = [
            'id' => 0,
            'title' => Task::$sHashFieldName,
            'name' => Task::$sHashFieldName,
            'group' => 0,
            'editor' => 'string',
            'widget' => '',
            'validator' => '',
            'def_value' => '',
            'attr_' . Attr::SHOW_IN_LIST => 0,
            'attr_' . Attr::SHOW_IN_DETAIL => 0,
            'attr_' . Attr::SHOW_IN_SORTPANEL => 0,
            'attr_' . Attr::ACTIVE => 0,
            'attr_' . Attr::MEASURE => 0,
            'attr_' . Attr::SHOW_IN_PARAMS => 0,
            'attr_' . Attr::SHOW_IN_TAB => 0,
            'attr_' . Attr::SHOW_IN_FILTER => 0,
            'attr_' . Attr::IS_UNIQ => 0,
            'attr_' . Attr::SHOW_IN_TABLE => 0,
            'attr_' . Attr::SHOW_IN_CART => 0,
            'attr_' . Attr::SHOW_TITLE => 0,
            'attr_' . Attr::SHOW_IN_MAP => 0,
        ];

        $oField = Card::getField(0);
        $oCard = Card::get(1);

        $oField->setData($aFieldData);
        $oField->entity = 1;
        $oField->save();

        foreach ($aFieldData as $sKey => $sValue) {
            if (!isset($oField->{$sKey}) && mb_strpos($sKey, 'attr_') === 0) {
                $oField->setAttr(mb_substr($sKey, 5), $sValue);
            }
        }

        $aFieldData = [
            'id' => 0,
            'title' => Task::$sUpdatedFieldName,
            'name' => Task::$sUpdatedFieldName,
            'group' => 0,
            'editor' => 'string',
            'widget' => '',
            'validator' => '',
            'def_value' => '',
            'attr_' . Attr::SHOW_IN_LIST => 0,
            'attr_' . Attr::SHOW_IN_DETAIL => 0,
            'attr_' . Attr::SHOW_IN_SORTPANEL => 0,
            'attr_' . Attr::ACTIVE => 0,
            'attr_' . Attr::MEASURE => 0,
            'attr_' . Attr::SHOW_IN_PARAMS => 0,
            'attr_' . Attr::SHOW_IN_TAB => 0,
            'attr_' . Attr::SHOW_IN_FILTER => 0,
            'attr_' . Attr::IS_UNIQ => 0,
            'attr_' . Attr::SHOW_IN_TABLE => 0,
            'attr_' . Attr::SHOW_IN_CART => 0,
            'attr_' . Attr::SHOW_TITLE => 0,
            'attr_' . Attr::SHOW_IN_MAP => 0,
        ];

        $oField = Card::getField(0);
        $oCard = Card::get(1);

        $oField->setData($aFieldData);
        $oField->entity = 1;
        $oField->save();

        foreach ($aFieldData as $sKey => $sValue) {
            if (!isset($oField->{$sKey}) && mb_strpos($sKey, 'attr_') === 0) {
                $oField->setAttr((int) mb_substr($sKey, 5), $sValue);
            }
        }

        $oCard->updCache();
    }

    // func

    public function uninstall()
    {
        $oCard = Card::get(1);

        $oField = Card::getFieldByName(1, Task::$sHashFieldName);

        $oField->delete();

        $oField = Card::getFieldByName(1, Task::$sUpdatedFieldName);

        $oField->delete();

        $oCard->updCache();

        return true;
    }

    // func

    public static function setTaskImport()
    {
        $scheduleItem = new Schedule();
        $command = ['class' => 'skewer\\components\\import\\Service',
            'method' => 'removeOldFiles',
            'parameters' => [], ];

        $command = json_encode($command);

        $aData = [
            'title' => 'Удаление старых файлов импорта',
            'name' => 'rmOldFilesImport',
            'command' => $command,
            'priority' => Task::priorityNormal,
            'resource_use' => Task::weightNormal,
            'target_area' => '3',
            'status' => '1',
            'c_min' => 1,
            'c_hour' => 1,
            'c_day' => '*',
            'c_month' => '*',
            'c_dow' => 5,
        ];

        $scheduleItem->setAttributes($aData);

        if (!$scheduleItem->save()) {
            throw new Exception(\Yii::t('import', 'task_not_add'));
        }
    }
}//class
