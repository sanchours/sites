<?php

use skewer\components\config\PatchPrototype;
use skewer\components\i18n\ModulesParams;

class Patch94496 extends PatchPrototype
{

    public $sDescription = 'Оповещение о результатах импорта';

    public $bUpdateCache = false;

    /**
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        Yii::$app->db->createCommand()
            ->addColumn('import_template', 'send_notify', 'int(1) NOT NULL DEFAULT 0')
            ->execute();

        ModulesParams::setParams('import', 'mail_notify_title', \Yii::$app->language, 'Результаты импорта');
        ModulesParams::setParams('import', 'mail_notify_body', \Yii::$app->language, '[info_result_import]');
        ModulesParams::setParams('import', 'mail_notify_mail_to', \Yii::$app->language, '');
        ModulesParams::setParams('import', 'mail_notify_is_send', \Yii::$app->language, 1);
    }

}
