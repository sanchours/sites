<?php

namespace skewer\build\Tool\Messages;

use skewer\base\orm\Query;
use skewer\components\config\InstallPrototype;

/**
 * @class MessagesToolInstall
 */
class Install extends InstallPrototype
{
    public function init()
    {
        return true;
    }

    public function install()
    {
        Query::SQL(
            "CREATE TABLE IF NOT EXISTS `messages` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` char(255) NOT NULL,
                `text` text NOT NULL,
                `type` int(1) NOT NULL COMMENT 'type of letter',
                `new` bit(1) NOT NULL DEFAULT b'1',
                `arrival_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `send_id` int(11) NOT NULL,
                `send_read` tinyint(1) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `for_new` (`new`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"
        );

        Query::SQL(
            'CREATE TABLE IF NOT EXISTS `messages_read` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `send_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'
        );

        Query::SQL(
            "INSERT INTO `schedule` (
                `id`,`title`,`name`,`command`,`priority`,`resource_use`,`target_area`,`status`,`c_min`,`c_hour`,
                `c_day`,`c_month`,`c_dow`)
            VALUES (30,'Отчет о прочтении','send_read','{\"class\":\"Tool\\Messages\\Service\",\"method\":\"sendRead\",
                \"parameters\":[]}', 2, 4, 3, 1, 30, NULL, NULL, NULL, NULL);"
        );

        Query::SQL(
            "INSERT INTO `group_policy_module` (`policy_id`,`module_name`,`title`)
             VALUES ((SELECT `id` FROM `group_policy` WHERE `alias`='admin' LIMIT 1), 'Messages','Сообщения');"
        );

        return true;
    }

    public function uninstall()
    {
        Query::SQL('DROP TABLE IF NOT EXISTS `messages`;');
        Query::SQL('DROP TABLE IF NOT EXISTS `messages_read`;');
        Query::SQL("DELETE FROM `schedule` WHERE `name`='send_read';");

        return true;
    }
}
