<?php

use skewer\base\site\Type;
use skewer\components\config\PatchPrototype;

class Patch67293 extends PatchPrototype
{
    public $sDescription = 'Изменение имен столбцов в cl_semantic';

    public $bUpdateCache = true;

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     *
     * @return bool|void
     */
    public function execute()
    {
        if (!Type::hasCatalogModule()) {
            return;
        }

        Yii::$app->db->createCommand('ALTER TABLE `cl_semantic` 
                                            CHANGE `parent_id` `child_id` int(11) NOT NULL,
                                            CHANGE `child_id` `parent_id` int(11) NOT NULL,
                                            CHANGE `parent_card` `child_card` int(11) NOT NULL,
                                            CHANGE `child_card` `parent_card` int(11) NOT NULL
                                            ')->execute();
    }
}
