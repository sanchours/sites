<?php

namespace skewer\components\design;

use skewer\components\forms\entities\FieldEntity;

class BackupFormParams extends BackupParams
{
    /**
     * Установит значение $sNameParam поля формы $oField.
     *
     * @param FieldEntity $fieldEntity - поле формы
     * @param string $sNameParam - название параметра поля
     */
    public function setParam4FieldForm(FieldEntity $fieldEntity, $sNameParam)
    {
        $this->aData[] = [
            'type' => 'set_param_for_field_form',
            'data' => [
                'form_id' => $fieldEntity->form_id,
                'nameField' => $fieldEntity->slug,
                'nameParam' => $sNameParam,
                'value' => $fieldEntity->{$sNameParam},
            ],
        ];
    }

    /**
     * Откатывает данные по внутреннему массиву.
     */
    public function revertData()
    {
        foreach ($this->aData as $aParam) {
            $type = $aParam['type'];
            $data = $aParam['data'];
            switch ($type) {
                case 'set_param_for_field_form':

                    FieldEntity::updateAll(
                        [$data['nameParam'] => $data['value']],
                        [
                            'form_id' => $data['form_id'],
                            'param_name' => $data['nameField'],
                        ]
                    );
                    break;
            }
        }

        parent::revertData();
    }
}
