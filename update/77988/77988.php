<?php

use skewer\components\catalog\Card;
use skewer\components\config\PatchPrototype;

class Patch77988 extends PatchPrototype
{
    public $sDescription = 'Изменение типа поля select в БД в каталоге';

    public $bUpdateCache = true;

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     *
     * @return bool|void
     */
    public function execute()
    {
        $aFields = \skewer\components\catalog\model\FieldTable::find()
            ->where('editor', ['select'])
            ->getAll();

        $aCard = [];
        if ($aFields) {
            /** @var \skewer\components\catalog\model\FieldRow $field */
            foreach ($aFields as $field) {
                if ($field->editor == \skewer\base\ft\Editor::SELECT) {
                    $field->size = 0;
                    $field->save();
                    if (!isset($aCard[$field->entity])) {
                        $aCard[$field->entity] = $field->entity;
                    }
                }
            }

            foreach ($aCard as $entity) {
                $entity = Card::get($entity);
                $entity->updCache();
            }
        }
    }
}
