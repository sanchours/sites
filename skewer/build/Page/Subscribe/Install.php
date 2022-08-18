<?php

namespace skewer\build\Page\Subscribe;

use skewer\components\config\InstallPrototype;
use skewer\components\forms\service\FormService;

class Install extends InstallPrototype
{
    /** @var FormService $_formService */
    private $_formService;

    public function init()
    {
        $this->_formService = new FormService();

        return true;
    }

    // func

    /**
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public function install()
    {
        //Добавим в шаблон нового раздела fastSubscribe

        $iTplNew = \Yii::$app->sections->getValue('tplNew');

        $sQuery = "INSERT INTO `parameters` ( `parent`, `group`, `name`, `value`, `title`, `access_level`, `show_val`) VALUES
                    ( '{$iTplNew}', 'fastSubscribe', 'layout', 'left,right,center', '', 0, ''),
                    ( '{$iTplNew}', 'fastSubscribe', 'object', 'Subscribe', '', 0, ''),
                    ( '{$iTplNew}', 'fastSubscribe', 'enable', '1', '', 0, ''),
                    ( '{$iTplNew}', 'fastSubscribe', 'mini', '1', '', 0, '');";

        \Yii::$app->db->createCommand($sQuery)->execute();

        if (!$this->_formService->hasFormWithSlug(SubscribeEntity::tableName())) {
            SubscribeEntity::createTable();
        }

        return true;
    }

    // func

    /**
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public function uninstall()
    {
        //Удалим метку "fastSubscribe"
        \Yii::$app->db->createCommand(
            'DELETE FROM `parameters` WHERE `group`="fastSubscribe"'
        )->execute();

        //Удаление из лаяутов метки
        \Yii::$app->db->createCommand(
            "UPDATE `parameters` SET `value`= REPLACE(`value`, 'fastSubscribe', '') WHERE `group`='.layout'"
        )->execute();

        return true;
    }

    // func
}
