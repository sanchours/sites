<?php

namespace skewer\base\ft;

use yii\web\ServerErrorHttpException;

/**
 * Набор вспомогательных функций
 * var 0.80.
 *
 * $Author$
 * $Revision$
 * $Date$
 */
class Fnc
{
    /**
     * Отдает true, если включен режим отладки.
     *
     * @static
     *
     * @return string
     */
    public static function isDebugMode()
    {
        return (bool) ini_get('display_errors');
    }

    /**
     * Выдать ошибку.
     *
     * @param \Exception|Exception $e
     * @param bool $num
     *
     * @throws \Exception
     */
    public static function error(\Exception $e, $num = false)
    {
        if (defined('IS_UNIT_TEST')) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
        if (self::isDebugMode()) {
            $exit = false;
            if (is_object($e) and (is_a($e, 'skewer\base\ft\Exception') or is_a($e, 'skewer\base\ft\exception\Inner'))) {
                $error_text = sprintf('%s (%s)', $e->getMessage(), $e->getCaller());
                $exit = $num;
            } else {
                $error_text = ($num !== false ? "№{$num} - " : '- ') . $e;
            }
            echo '<br /><strong>Error in ftConstructor:</strong> ' . $error_text . '<br />';
            if ($exit) {
                while (@ob_end_flush());
                exit;
            }
        }
    }

    /**
     * Определяет, есть ли языковой набор на сайте.
     *
     * @static
     *
     * @return bool
     */
    public static function hasLanguages()
    {
        global $languages;

        return (bool) $languages;
    }

    /**
     * Отдает список зарезервированных имен.
     *
     * @static
     *
     * @return array
     */
    public static function reservedNames()
    {
        return [
            'id',
            '_id',
            '_lang',
            '_parent',
            '__path',
            '__entity_name',
            '_group',
            '_parent_entity',
            '_add_date',
            '_upd_date',
            '__sub_select',
            '_delete_row',
        ];
    }

    /**
     * Отдает список процессоров полей.
     *
     * @static
     *
     * @return array
     */
    public static function processorTypes()
    {
        return [
            'w' => 'widget',
            'v' => 'validator',
            'm' => 'modificator',
        ];
    }

    /**
     * Отдает флаг eg модель задана.
     *
     * @static
     *
     * @return bool
     */
    public static function egInited()
    {
        return false;
    }

    /**
     * приводит входную переменную к массиву.
     *
     * @static
     *
     * @param $input
     *
     * @return array
     */
    public static function toArray($input)
    {
        $output = [];

        // если строка
        if (is_string($input)) {
            $input = explode(',', $input);
        }

        // если массив
        if (is_array($input)) {
            // удаление лишних пробелов
            $output = array_map('trim', $input);
        }

        return $output;
    }

    /**
     * сбор инфирмации о запросах в режиме отладки.
     *
     * @static
     *
     * @param $query
     */
    public static function queryDebuger($query)
    {
        if (self::isDebugMode()) {
            global $ft_query_debuger;
            $ft_query_debuger[] = $query;
        }
    }

    /**
     * раскраска запроса.
     *
     * @static
     *
     * @param $query
     *
     * @return mixed
     */
    public static function formatQuery($query)
    {
        // таблица замены
        $replace_arr = [
            '`,`' => "`,\n  `",
            "', `" => "', \n  `",
            ' AS ' => ' <strong>AS</strong> ',
            ' ON ' => ' <strong>ON</strong> ',
            ' AND ' => ' <strong>AND</strong> ',
            'SELECT  `' => "<strong>SELECT</strong>\n  `",
            'SELECT `' => "<strong>SELECT</strong>\n  `",
            'SELECT SQL_CALC_FOUND_ROWS' => "<strong>SELECT</strong> SQL_CALC_FOUND_ROWS\n",
            'UPDATE `' => '<strong>UPDATE</strong> `',
            'DELETE ' => '<strong>DELETE</strong> ',
            'INSERT INTO `' => '<strong>INSERT</strong> INTO `',
            '`) VALUES ( ' => "`) <strong>\nVALUES\n</strong> ( ",
            '` SET  `' => "` <strong>\nSET</strong>\n  `",
            ' FROM' => "\n<strong>FROM</strong>",
            ' LEFT JOIN' => "\n<strong>LEFT JOIN</strong>",
            ' RIGHT JOIN' => "\n<strong>RIGHT JOIN</strong>",
            ' WHERE' => "\n<strong>WHERE</strong>",
            ' ORDER BY' => "\n<strong>ORDER BY</strong>",
            ' GROUP BY' => "\n<strong>GROUP BY</strong>",
            ' LIMIT' => "\n<strong>LIMIT</strong>",
        ];

        // раскраска
        return $query = str_replace(array_keys($replace_arr), array_values($replace_arr), $query);
    }

    /**
     * отобразить все ft запросы.
     *
     * @static
     */
    public static function showQueryLog()
    {
        echo '<strong>============================<br>Total query log </strong><br>';

        global $ft_query_debuger;
        if (is_array($ft_query_debuger)) {
            foreach ($ft_query_debuger as $key => $value) {
                $q = self::formatQuery($value);
                echo  '<pre><strong>Query:</strong> № ' . ($key + 1) . ":\n";
                print_r($q);
                echo '</pre>_______________';
            }
        } // foreach

        echo '<br><strong>End total query log.<br>============================</strong><br />';
    }

    /**
     * вернуть общее число ft запросов.
     *
     * @static
     *
     * @return int
     */
    public static function getQueryCnt()
    {
        global $ft_query_debuger;

        return is_array($ft_query_debuger) ? count($ft_query_debuger) : 0;
    }

    /**
     * вернуть типы запросов с количеством
     *
     * @static
     *
     * @return string
     */
    public static function getQueryTypeCnt()
    {
        // переменная счетчика
        global $ft_query_debuger;
        if (!$ft_query_debuger) {
            return '';
        }

        // заготовка для вывода
        $out = [
            's' => 0,
            'i' => 0,
            'u' => 0,
            'd' => 0,
        ];

        // посчитать
        foreach ($ft_query_debuger as &$query) {
            $l = mb_strtolower($query[0]);
            $out[$l] = isset($out[$l]) ? $out[$l] + 1 : 1;
        }

        // отформатировать вывод
        foreach ($out as $l => &$val) {
            $val .= $l;
        }

        return implode(' ', $out);
    }

    /**
     * отдает отфтрматированный объем использованной памяти.
     *
     * @static
     *
     * @param bool $real
     *
     * @return string
     */
    public static function memoryUsage($real = false)
    {
        $size = memory_get_usage($real);
        $unit = ['б', 'Кб', 'Мб', 'Гб', 'Тб', 'Пб'];

        return @round($size / 1024 ** ($i = (int) floor(log($size, 1024))), 2) . ' ' . $unit[$i];
    }

    /**
     * Сообщает есть ли поле в таблице.
     *
     * @param string $sTable имя таблицы
     * @param string $sField имя поля
     *
     * @throws ServerErrorHttpException
     *
     * @return bool
     */
    public static function tableHasField($sTable, $sField)
    {
        // список полей
        $rResult = \Yii::$app->db->createCommand('SHOW COLUMNS FROM `' . $sTable . '`')->queryAll();

        $aErrInfo = \Yii::$app->db->pdo->errorInfo();
        $sError = (string) $aErrInfo[1];

        if ($sError) {
            throw new ServerErrorHttpException('Db error: ' . $sError);
        }
        foreach ($rResult as $aField) {
            if ($aField['Field'] == $sField) {
                return true;
            }
        }

        return false;
    }
}
