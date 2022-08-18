<?php

namespace skewer\build\Tool\Backup;

use skewer\base\log\Logger;
use skewer\base\orm\Query;
use skewer\base\queue\ar\Schedule;
use skewer\base\queue as QM;
use skewer\components\gateway;
use skewer\helpers\Files;
use yii\base\UserException;
use yii\db\Exception;

/**
 * API работы с резевным копированием
 */
class Api
{
    public static function getListItems()
    {
        return Service::getBackupList();
    }

    /**
     * Отдает наличие задачи на ручной бекап
     *
     * @return bool
     */
    public static function hasBackupTask()
    {
        $bHasTask = (bool) QM\ar\Task::find()
            ->where('title', 'create user backup')
            ->andWhere('status', QM\Task::stProcess)
            ->getCount();

        return $bHasTask;
    }

    /**
     * Проверка бэкапа.
     *
     * @param $iBackupId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function checkBackup($iBackupId)
    {
        $oClient = gateway\Api::createClient();

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'checkBackup', [$iBackupId], static function ($mResult, $mError) {
            if (isset($mResult['error'])) {
                throw new \Exception($mResult['error']);
            }

            if ($mError) {
                throw new \Exception($mError);
            }
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return true;
    }

    /**
     * обновление времени запуска создания резервной копии (используется сервисом sms).
     *
     * @static
     *
     * @param string $sTime
     *
     * @throws UserException
     *
     * @return bool
     */
    public static function updBackupTime($sTime)
    {
        $aTaskGlobalTime = explode(':', $sTime);

        $mTaskId = Schedule::getIdByName('make_backup');

        // постановка/снятие задачи в планировщике

        if (!$scheduleItem = Schedule::findOne($mTaskId)) {
            $scheduleItem = new Schedule();
            $command = ['class' => 'skewer\build\Tool\Backup\Service',
                'method' => 'makeBackup',
                'parameters' => [], ];

            $command = json_encode($command);

            $aData = [
                'title' => 'Создание резервной копии',
                'name' => 'make_backup',
                'command' => $command,
                'priority' => '1',
                'resource_use' => '7',
                'target_area' => '3',
                'status' => '1',
                'c_min' => $aTaskGlobalTime[1],
                'c_hour' => $aTaskGlobalTime[0],
                'c_day' => null,
                'c_month' => null,
                'c_dow' => null,
            ];
        } else {
            $aData = [
                'c_min' => $aTaskGlobalTime[1],
                'c_hour' => $aTaskGlobalTime[0],
            ];
        }

        $scheduleItem->setAttributes($aData);

        if (!$scheduleItem->save()) {
            throw new UserException($scheduleItem);
        }

        return true;
    }

    /**
     * установка параметров резервного копирования.
     *
     * @static
     *
     * @param array $aSetting
     *
     * @throws \Exception|gateway\Exception
     *
     * @return bool
     */
    public static function setBackupSetting($aSetting)
    {
        $oClient = gateway\Api::createClient();

        $aParam = [$aSetting];

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'setLocalBackupSetting', $aParam, static function ($mResult, $mError) {
            if ($mError) {
                throw new \Exception($mError);
            }
        });

        if (!$oClient->doRequest()) {
            throw new gateway\Exception('Ошибка соединения с SMS');
        }
        $mTaskId = Schedule::getIdByName('make_backup');

        // постановка/снятие задачи в планировщике
        if (!$mTaskId) {
            $aGlobalSetting = Service::getBackupGlobalSetting();
            $aTaskGlobalTime = explode(':', $aGlobalSetting['bs_time']);

            $scheduleItem = new Schedule();
            $command = ['class' => 'skewer\build\Tool\Backup\Service',
                'method' => 'makeBackup',
                'parameters' => [], ];

            $command = json_encode($command);

            $aData = [
                'title' => 'Создание резервной копии',
                'name' => 'make_backup',
                'command' => $command,
                'priority' => '1',
                'resource_use' => '7',
                'target_area' => '3',
                'status' => '1',
                'c_min' => $aTaskGlobalTime[1],
                'c_hour' => $aTaskGlobalTime[0],
                'c_day' => null,
                'c_month' => null,
                'c_dow' => null,
            ];

            $scheduleItem->setAttributes($aData);

            if (!$scheduleItem->save()) {
                throw new UserException($scheduleItem);
            }
        }

        return true;
    }

    /**
     * создание новой резервной копии.
     *
     * @static
     *
     * @param mixed $sComment
     *
     * @throws \Exception|gateway\Exception
     *
     * @return array
     */
    public static function createNewBackup($sComment = '')
    {
        $aConfig = [
            'class' => '\skewer\base\queue\MethodTask',
            'priority' => QM\Task::priorityHigh,
            'resource_use' => QM\Task::weightCritic,
            'title' => 'create user backup',
            'parameters' => [
                'class' => 'skewer\build\Tool\Backup\Service',
                'method' => 'makeBackup',
                'parameters' => ['user', $sComment],
            ],
        ];

        return QM\Task::runTask($aConfig, 0, false);
    }

    /**
     * удаление ранее созданной резервной копии.
     *
     * @static
     *
     * @param array $aData
     *
     * @throws \Exception|gateway\Exception
     *
     * @return bool
     */
    public static function removeBackup($aData)
    {
        $oClient = gateway\Api::createClient();

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'removeBackup', [$aData['id']], static function ($mResult, $mError) {
            if ($mError) {
                throw new \Exception($mError);
            }
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return true;
    }

    /**
     * запрос на восстановление сайта из резервной копии.
     *
     * @static
     *
     * @param array $aData
     *
     * @throws \Exception|gateway\Exception
     *
     * @return bool
     */
    public static function recoverBackup($aData)
    {
        $oClient = gateway\Api::createClient();

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'recoverBackup', $aData, static function ($mResult, $mError) {
            if ($mError) {
                throw new \Exception($mError);
            }
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return true;
    }

    /**
     * @param $path
     *
     * @throws \Exception
     */
    public static function createDBbackup($path)
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        } else {
            ini_set('max_execution_time', 0);
        }

        $fcache = '';
        $tablesQuery = Query::SQL('SHOW TABLES');
        $h = fopen($path, 'wb+');

        /**
         * набор таблиц. Структура
         *   ключ - имя таблицы
         *   значение - массив
         *     done - флаг того, что обработано
         *     query - запрос для установки таблицы
         *     depends - массив имен созависимых таблиц.
         *
         * Прокручивать будем весь массив таблиц несколько раз.
         * Если созависимые не обработаны - пропускаем
         * Крутим до тех пор, пока добавляются данные в файл
         */
        $tables = [];

        while ($table = $tablesQuery->fetchArray()) {
            $tableName = end($table);
            $r = Query::SQL("SHOW CREATE TABLE `{$tableName}`");
            $item = $r->fetchArray();

            $createQuery = $item['Create Table'];

            preg_match_all('/REFERENCES `([-\w]*)`/i', $createQuery, $matches);

            $tables[$tableName] = [
                'done' => false,
                'query' => $createQuery,
                'depends' => $matches[1],
            ];
        }

        do {
            $hasModification = false;

            foreach ($tables as $tableName => $table) {
                // если уже обработана - пропускаем
                if ($tables[$tableName]['done']) {
                    continue;
                }

                // если не обработаны созависимые таблицы - пропускаем
                $skip = false;

                foreach ($table['depends'] as $tableDepends) {
                    if (!$tables[$tableDepends]['done']) {
                        $skip = true;
                    }
                }

                if ($skip) {
                    continue;
                }

                // флаг того, что таблица обработана
                $tables[$tableName]['done'] = true;

                // флаг наличия модификаций в проходе
                $hasModification = true;

                $fcache .= "#\tTC`{$tableName}`\t;\n{$table['query']}\t;\n";

                // Определяем типы полей
                $notNum = [];
                $r = Query::SQL("SHOW COLUMNS FROM `{$tableName}`");
                $fields = 0;
                while ($col = $r->fetchArray()) {
                    $notNum[$fields] = preg_match('/^(tinyint|smallint|mediumint|bigint|int|float|double|real|decimal|numeric|year)/', $col['Type']) ? 0 : 1;
                    ++$fields;
                }

                $aCnt = Query::SQL("SELECT count(*) as cnt FROM `{$tableName}`;")->fetchArray();
                if ($aCnt['cnt'] == 0) {
                    continue;
                }

                $i = 0;
                $sline = 0;
                $eline = 100;

                while ($i <= $aCnt['cnt']) {
                    $fcache .= "#\tTD`{$tableName}`\t;\nINSERT INTO `{$tableName}` VALUES \n";
                    $r = Query::SQL("SELECT * FROM `{$tableName}` LIMIT {$sline}, {$eline};");

                    while ($row = $r->fetchArray()) {
                        $aFields = [];
                        for ($k = 0; $k < $fields; ++$k) {
                            $field = array_shift($row);
                            if ($field === null) {
                                $field = '\N';
                            } elseif ($notNum[$k]) {
                                $field = \Yii::$app->db->pdo->quote($field);
                            }

                            $aFields[] = $field;
                        }
                        $fcache .= '(' . implode(',', $aFields) . "),\n";
                    }

                    $i += $eline;
                    $sline += $eline;

                    $fcache = substr_replace($fcache, "\t;\n", -2, 2);
                    if (mb_strlen($fcache) >= 61440) {
                        fwrite($h, $fcache);
                        $fcache = '';
                    }
                }// while data
            }
        } while ($hasModification);

        fwrite($h, $fcache);
        fclose($h);
    }

    /**
     * @param string $filepath
     * @param string $filter
     *
     * @return array
     */
    public static function getDumpFiles($filepath = '', $filter = 'sql')
    {
        $out = [];
        if (empty($filepath)) {
            return $out;
        }
        if (!is_dir($filepath)) {
            return $out;
        }
        $d = dir($filepath);
        if ($d->handle) {
            while (false !== ($entry = $d->read())) {
                if ($entry != '.' and $entry != '..') {
                    if (mb_strtolower($filter) == mb_strtolower(self::geText($entry))) {
                        $out[$entry] = ['filename' => $entry, 'filesize' => Files::getFileSize($filepath . '/' . $entry)];
                    }
                }
            }
        }// h
        $d->close();

        return $out;
    }

    // func

    /**
     * перенесено с двойки, форматирование названия.
     *
     * @param string $filename
     *
     * @return string
     */
    public static function geText($filename = '')
    {
        $out = '';
        if (empty($filename)) {
            return $out;
        }
        $filename = explode('.', $filename);
        count($filename) ? $out = $filename[count($filename) - 1] : $out = '';

        return $out;
    }

    // func

    /**
     * Восстановление БД из админки на стороннем хостинге.
     *
     * @param $fileName string Имя и путь до файла восстановления
     *
     * @return bool Результат восстановления
     */
    public static function restoreDBase($fileName)
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        } else {
            ini_set('max_execution_time', 0);
        }

        self::truncateDbase();

        $iDataChunkLength = 16384;
        $linesPerSession = 3000;
        $string_quotes = '\'';
        $max_query_lines = 2000;
        $comment = ['/*!', '--'];
        $delimiter = ';';

        $query = ''; // Запрос
        $queries = 0; // Количество запросов
        $totalQueries = 0; // Общее количесвто запросов
        $lineNumber = 0; // Номер строки
        $queryLines = 0;
        $inParents = false; // Дочерний ли элемент
        $error = false; // Ошибка при выполнении запроса в бд

        $start = 0;
        $file = fopen($fileName, 'r');

        while ($lineNumber < $start + $linesPerSession || $query != '') {
            $dumpLine = '';
            while (!feof($file) && mb_substr($dumpLine, -1) != "\n" && mb_substr($dumpLine, -1) != "\r") {
                $dumpLine .= fgets($file, $iDataChunkLength);
            }

            if ($dumpLine === '') {
                break;
            }

            // Переделываем переносы строки из DOS b Mac.(I don't know if it really works on Win32 or Mac Servers)
            $dumpLine = str_replace("\r\n", "\n", $dumpLine);
            $dumpLine = str_replace("\r", "\n", $dumpLine);

            if (!$inParents && mb_strpos($dumpLine, 'DELIMITER ') === 0) {
                $delimiter = str_replace('DELIMITER ', '', trim($dumpLine));
            }

            // Пропускаем коменнтарии и пустые линни, если это не родители
            if (!$inParents) {
                $skipLine = false;
                reset($comment);
                foreach ($comment as $comment_value) {
                    if (trim($dumpLine) == '' || mb_strpos(trim($dumpLine), $comment_value) === 0) {
                        $skipLine = true;
                        continue;
                    }
                }
                if ($skipLine) {
                    ++$lineNumber;
                    continue;
                }
            }

            // Удаляем двойные обраные слэши из $dumpLine
            $dumpLine_delSlashed = str_replace('\\\\', '', $dumpLine);

            // Считаем ' and \' (or " and \") в $dumpLine пустыми, чтобы избежать разрыва запроса в текстовом поле, заканчивающимся на $delimetr

            $parents = mb_substr_count($dumpLine_delSlashed, $string_quotes) - mb_substr_count($dumpLine_delSlashed, "\\{$string_quotes}");
            if ($parents % 2 != 0) {
                $inParents = !$inParents;
            }

            // Добавляем строку в запрос, если она все вышестоящие условия
            $query .= $dumpLine;

            // Считаем строку только у родителя (Текстовые поля могут включать неограниченные разрывы строк)
            if (!$inParents) {
                ++$queryLines;
            }

            // Остнавливаемся, если запрос содержит больше строк чем $max_query_lines
            if ($queryLines > $max_query_lines) {
                continue;
            }

            // Выполняем запрос, если строка является концом запроса
            if ((preg_match('/' . $delimiter . '$/', trim($dumpLine)) || $delimiter == '') && !$inParents) {
                // Вырезаем разделитель $delimiter в конце запроса
                $query = mb_substr(trim($query), 0, -1 * mb_strlen($delimiter));

                // Выполняем запрос
                try {
                    \Yii::$app->db->createCommand($query)->execute();
                } catch (Exception $e) {
                    Logger::error('Recovery error: ' . $e->getMessage());
                    $error = true;
                }

                unset($query, $dumpLine);

                ++$totalQueries;
                ++$queries;
                $query = '';
                $queryLines = 0;
            }
            ++$lineNumber;
            ++$start;
        }

        return !$error;
    }

    private static function truncateDbase()
    {
        do {
            $tables = Query::SQL('SHOW TABLES;');
            $aTableNames = [];
            $bFlagDel = false;
            while ($row = $tables->fetchArray()) {
                $bFlagDel = true;
                $tableName = end($row);
                $aTableNames[] = "`{$tableName}`";

                try {
                    Query::SQL('DROP TABLE IF EXISTS ' . implode(',', $aTableNames) . ' ;');
                } catch (\Exception $e) {
                }
            }
        } while ($bFlagDel);
    }

    // func
}
