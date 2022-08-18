<?php

namespace skewer\components\crm;

require_once __DIR__ . '/CrmSender.php';

/**
 * Класс служит только для корректной инициализации CrmSender через
 * стандартный набор namespace проекта.
 * В случае, если CrmSender будет вынесен в Composer, надобность в этом классе отпадет
 */
class Crm extends \CrmSender
{
}
