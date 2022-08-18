<?php

namespace skewer\build\Design\Inheritance;

use skewer\base\orm\Query;

class Api
{
    /** @var array Кэш для групп */
    private static $groups = [];

    /**
     * Возвращает список исключений.
     *
     * @return array
     */
    public static function getParamLinks()
    {
        $query = "SELECT `p1`.`title` AS `basic`,
                         `p2`.`title` AS `extend`,
                         `p1`.`name` AS `p_basic`,
                         `p2`.`name` AS `p_extend`,
                         CONCAT(`p1`.`id`,'_',`p2`.`id`) AS `link_ids`,
                         `p1`.`group` AS `ancestor_group`,
                         `p2`.`group` AS `descendant_group`,
                         `cdr`.`active`,
                         `p2`.`id` AS `id`
                    FROM `css_data_references` AS `cdr`
              INNER JOIN `css_data_params` AS `p1`
                      ON `cdr`.`ancestor`=`p1`.`name`
              INNER JOIN `css_data_params` AS `p2`
                      ON `cdr`.`descendant`=`p2`.`name`";
        //WHERE `cdr`.`active`=0;";

        $oResult = Query::SQL($query);

        $exceptions = [];
        while ($aRow = $oResult->fetchArray()) {
            $aRow['extend'] = implode(' - ', array_reverse(self::getParamTitlePathAsArray($aRow['descendant_group']))) . ' - ' . $aRow['extend'];
            $aRow['basic'] = implode(' - ', array_reverse(self::getParamTitlePathAsArray($aRow['ancestor_group']))) . ' - ' . $aRow['basic'];

            $exceptions[] = $aRow;
        }

        return $exceptions;
    }

    /**
     * Возвращает путь к параметру.
     *
     * @param $groupId
     *
     * @return array
     */
    public static function getParamTitlePathAsArray($groupId)
    {
        $groups = [];

        $group = [];
        $group['parent'] = $groupId;

        do {
            if (isset(self::$groups[$group['parent']])) {
                $group = self::$groups[$group['parent']];
            } else {
                $oResult = Query::SQL(
                    'SELECT `id`,`title`,`parent` FROM `css_data_groups` WHERE `id`=:parent;',
                    ['parent' => $group['parent']]
                );

                $group = $oResult->fetchArray();
                if ($group) {
                    self::$groups[$group['id']] = $group;
                }
            }

            if ($group) {
                $groups[] = $group['title'];
            }
        } while ($group['parent']);

        return $groups;
    }
}
