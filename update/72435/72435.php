<?php

use skewer\components\config\PatchPrototype;
use skewer\components\forms\components\fields\Delimiter;
use skewer\components\forms\entities\FieldEntity;

class Patch72435 extends PatchPrototype
{
    public $sDescription = 'Переработка полей форм';

    public $bUpdateCache = false;

    public function execute()
    {
        $this->executeSQLQuery(
            'ALTER TABLE `forms_parameters`
                    DROP `param_section_id`, 
                    DROP `param_value`, 
                    DROP `param_depend`, 
                    DROP `no_send`;'
        );

        $this->executeSQLQuery(
            'ALTER TABLE `forms_parameters` 
                    CHANGE `param_id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT, 
                    CHANGE `param_name` `slug` VARCHAR (255) NOT NULL, 
                    CHANGE `param_title` `title` VARCHAR (255) NOT NULL, 
                    CHANGE `param_type` `type` VARCHAR (255) NOT NULL, 
                    CHANGE `param_required` `required` TINYINT (1), 
                    CHANGE `group` `group_prev_field` TINYINT (1),  
                    CHANGE `param_priority` `priority` TINYINT (4), 
                    CHANGE `param_default` `default` TEXT, 
                    CHANGE `param_maxlength` `max_length` INT (11), 
                    CHANGE `param_validation_type` `type_valid` CHAR (50), 
                    CHANGE `param_man_params` `spec_style` CHAR (255), 
                    CHANGE `field_class` `class_modify` VARCHAR (255),
                    CHANGE `param_description` `description` TEXT,
                    CHANGE `view_type` `display_type` INT (1);'
        );

        $this->executeSQLQuery(
            'ALTER TABLE `forms_parameters` RENAME {{%form_field}}'
        );

        // Все типы (валидации и типы полей) с большой буквы обрабатываются теперь
        $nameTable = FieldEntity::tableName();
        $this->executeSQLQuery(
            "UPDATE {$nameTable} SET 
                `type` = CONCAT(UPPER(LEFT(`type`,1)),SUBSTR(`type`,2)),
                `type_valid` = CONCAT(UPPER(LEFT(`type_valid`,1)),SUBSTR(`type_valid`,2))
                "
        );

        $this->executeSQLQuery(
            "ALTER TABLE `form_field` CHANGE COLUMN `type_valid` `type_of_valid` VARCHAR(255) DEFAULT ''"
        );

        // Изменение у типа delimiter типа - без типа валидации должно быть
        $delimiter = new Delimiter();
        FieldEntity::updateAll(['type_of_valid' => ''], ['type' => $delimiter->getName()]);
    }
}
