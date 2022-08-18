<?php

use skewer\build\Page\Cart\OrderOneClickEntity;
use skewer\build\Page\FAQ\FaqEntity;
use skewer\build\Page\GuestBook\ReviewEntity;
use skewer\build\Page\Subscribe\SubscribeEntity;
use skewer\components\config\PatchPrototype;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\entities\FieldEntity;
use skewer\components\forms\forms\TypeFieldForm;
use skewer\components\forms\components\fields\File;

class Patch72435_2 extends PatchPrototype
{
    public $sDescription = 'Переработка формы - основной таблицы';

    public $bUpdateCache = false;

    public function execute()
    {
        //перенос значений
        // из form.form_redirect в formAdd.redirect_link,
        // из form.form_succ_answer в formAdd.success_answer

        $this->executeSQLQuery(
            'ALTER TABLE `forms_add_data` 
                    ADD `success_answer` VARCHAR (255),
                    ADD `redirect_link` VARCHAR (255)
                    ;'
        );

        $result = $this->executeSQLQuery(
            'SELECT `form_id`, `form_redirect`, `form_succ_answer` FROM `forms`'
        );

        while ($select = $result->fetchArray()) {
            $this->executeSQLQuery(
                'UPDATE `forms_add_data` 
                        SET `success_answer` = :success_answer, `redirect_link` = :redirect_link
                        WHERE `form_id` = :form_id',
                [
                    'form_id' => $select['form_id'],
                    'success_answer' => $select['form_succ_answer'],
                    'redirect_link' => $select['form_redirect'],
                ]
            );
        }

        //удаление лишних столбцов
        $this->executeSQLQuery(
            'ALTER TABLE `forms` 
                    DROP COLUMN `form_redirect`, 
                    DROP COLUMN `form_succ_answer`,
                    DROP `form_active`;'
        );

        //переименование
        $this->executeSQLQuery(
            'ALTER TABLE `forms` 
                    CHANGE `form_id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT, 
                    CHANGE `form_name` `slug` VARCHAR (255) NOT NULL, 
                    CHANGE `form_title` `title` VARCHAR (255) NOT NULL, 
                    CHANGE `form_handler_type` `handler_type` VARCHAR (255) NOT NULL, 
                    CHANGE `form_handler_value` `handler_value` VARCHAR (255) NOT NULL, 
                    CHANGE `form_captcha` `captcha` TINYINT (1), 
                    CHANGE `form_hide_field` `hide_field` TINYINT (1), 
                    CHANGE `form_block_js` `block_js` TINYINT (1), 
                    CHANGE `form_answer` `answer` TINYINT (1), 
                    CHANGE `form_agreed` `agree` TINYINT (1), 
                    CHANGE `form_target` `target_yandex` VARCHAR (255), 
                    CHANGE `form_target_google` `target_google` VARCHAR (255), 
                    CHANGE `form_send_crm` `crm` TINYINT (1), 
                    CHANGE `form_template` `template` VARCHAR (255),
                    CHANGE `form_show_required_fields` `show_required_fields` TINYINT (1),
                    CHANGE `form_show_header` `show_header` TINYINT (1),
                    CHANGE `form_sys` `system` TINYINT (1),
                    CHANGE `replyTo` `email_in_reply` TINYINT (1),
                    CHANGE `form_button` `button` VARCHAR (255),
                    CHANGE `form_notific` `no_send_data_in_letter` TINYINT (1),
                    CHANGE `form_type_result_page` `type_result_page` INT (1),
                    CHANGE `form_class` `class` VARCHAR (255),
                    CHANGE `check_back` `show_check_back` TINYINT (1)
                    ;'
        );

        $this->executeSQLQuery(
            'ALTER TABLE `forms` RENAME {{%form}}'
        );

        $this->executeSQLQuery(
            'ALTER TABLE `forms_add_data` RENAME {{%form_extra_data}}'
        );

        if (\Yii::$app->db->getTableSchema('forms_links', true) !== null) {
            $this->executeSQLQuery(
                'ALTER TABLE `forms_links` RENAME {{%form_link}}'
            );
        }

        //изменение классов обработчиков
        $oneClickForm = FormEntity::getBySlug(OrderOneClickEntity::tableName());
        if ($oneClickForm instanceof FormEntity) {
            $oneClickForm->handler_value = OrderOneClickEntity::class;
            $oneClickForm->save();
        }

        $faqForm = FormEntity::getBySlug(FaqEntity::tableName());
        if ($faqForm instanceof FormEntity) {
            $faqForm->handler_value = FaqEntity::class;
            $faqForm->save();
        }

        $reviewForm = FormEntity::getBySlug(ReviewEntity::tableName());
        if ($reviewForm instanceof FormEntity) {

            $type = new File();
            FieldEntity::updateAll(
                ['type_of_valid' => TypeFieldForm::TYPE_VALID_FILE],
                ['form_id' => $reviewForm->id, 'type' => $type->getName()]
            );

            $reviewForm->handler_value = ReviewEntity::class;
            $reviewForm->save();
        }

        $subscribeForm = FormEntity::getBySlug(SubscribeEntity::tableName());
        if ($subscribeForm instanceof FormEntity) {
            $subscribeForm->handler_value = SubscribeEntity::class;
            $subscribeForm->save();
        }
    }
}
