<?php

namespace skewer\components\design;

use Exception;

/**
 * Класс для разбора css файлов и вычленения из них меток
 * Также производит сохранение этих данных в базу.
 */
class CssParser
{
    /** Переменная, которая хранит массив групп, упоминающихся в css-файлах
     * @var array
     */
    private $aGroups = [];

    /** Переменная, которая хранит массив параметров, упоминающихся в css-файлах
     * @var array
     */
    public $aParams = [];

    /**
     * Содержит массив связей между парамерами.
     *
     * @var array
     */
    protected $aReferences = [];

    /** @var int вес по умолчанию для сортировки файлов */
    protected $iDefaultWeight = 1;

    /**
     * Символ-детектор ссылки.
     *
     * @var string
     */
    protected $refSymbol = '~';

    /** Функция импорта CSS-параметров конкретного файла
     * @param $sPathFile
     *
     * @throws Exception
     *
     * @return bool
     */
    public function analyzeFile($sPathFile)
    {
        // Если запрашиваемого файла не существует - выдать исключение
        if (!file_exists($sPathFile)) {
            return false;
        }

        // Открываем файл
        $rFile = fopen($sPathFile, 'r');
        $sFile = '';

        // Чтение файла
        while (!feof($rFile)) {
            $sFile .= fread($rFile, 8000);
        }

        // Ищем совпадение по паттерну
        // depParam - временно выключено
        preg_match_all('/\/\*{1}\s*(?<command>layer|group|param|parentParam|const)\:(?<content>.*)\*\/{1}/xUi', $sFile, $aMatches);

        if (isset($aMatches['command']) && count($aMatches['command'])) {
            $sCurrentGroup = '';
            $sCurrentParam = '';
            $sCurrentLayer = '';

            // Проходимся по массиву совпадений, по ветке комманд
            foreach ($aMatches['command'] as $iKey => $sCommand) {
                // Делим значение для комманды по |
                $aLineParts = explode('|', $aMatches['content'][$iKey]);
                array_walk($aLineParts, static function (&$value) { $value = trim($value); });

                // Свитч по комманде
                switch ($sCommand) {
                    case 'layer':

                        // Установить текущий слой

                        /* layer: default */

                        $sCurrentLayer = $aLineParts[0];
                    break;

                    case 'group':

                        /*
                         * Добавить группу в массив групп
                         * Установить текущую группу
                         */

                        /* group: base.content | Контент */

                        if (isset($aLineParts[1])) {
                            $this->aGroups[$sCurrentLayer][$aLineParts[0]] = $aLineParts[1];
                        }
                        $sCurrentGroup = $aLineParts[0];
                    break;

                    case 'param':

                        /*
                         * Добавить параметр в массив параметров
                         */

                        /* param: color_text | Основной цвет текста | color | #707070 */

                        $sCurrentParam = $aLineParts[0];
                        $paramName = $sCurrentGroup . '.' . $aLineParts[0];
                        $this->aParams[$sCurrentLayer][$paramName] = [
                            'title' => $aLineParts[1],
                            'type' => $aLineParts[2],
                            'default' => $aLineParts[3],
                        ];

                        if ($ancestor = $this->isReference($aLineParts[3])) {
                            $this->aReferences[$ancestor][] = $paramName;
                        }

                    break;

//                    case 'depParam':
//
//                        /* depParam: default..menu.left.level2.active.link.color */
//
//                        if(isset($aLineParts[0]) AND !empty($aLineParts[0]) AND !empty($sCurrentGroup) AND !empty($sCurrentParam))
//                            $this->aReferences[$sCurrentLayer.'..'.$sCurrentGroup.'.'.$sCurrentParam][] = $aLineParts[0];
//
//                    break;

                    case 'parentParam':

                        /* parentParam: default..base.base_font-family */

                        if (isset($aLineParts[0]) and !empty($aLineParts[0]) and !empty($sCurrentGroup) and !empty($sCurrentParam)) {
                            $this->aReferences[$aLineParts[0]][] = $sCurrentLayer . '..' . $sCurrentGroup . '.' . $sCurrentParam;
                        }

                    break;
                }
            }

            return true;
        }

        return false;
    }

    //function analyzeCSSFiles()

    /**
     * Возвращает имя параметра, на который указывает ссылка либо false в случае если ссылка не найдена.
     *
     * @param $value
     *
     * @return bool|string
     */
    protected function isReference($value)
    {
        return (mb_stripos($value, $this->refSymbol) === 0) ? mb_substr($value, 1) : false;
    }

    //************************* CSS MATH PARSING ******************************************\

    /**
     * массив сигнатур матиматических операций в порядке убывания приоритетов выполнения.
     *
     * @return array
     */
    public function getMathOperation()
    {
        return [' / ', ' * ', ' - ', ' + '];
    }

    /**
     * проверяет наличие в строке сигнатуры математической операции.
     *
     * @param $sExp
     *
     * @return bool
     */
    public function isMathString($sExp)
    {
        $aOperations = $this->getMathOperation();
        foreach ($aOperations as $sOperation) {
            if (mb_strpos($sExp, $sOperation) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает найденую сигнатуру с максимальным приоритетом
     *
     * @param $sExp
     *
     * @return bool|string
     */
    public function findMathOperation($sExp)
    {
        $aOperations = $this->getMathOperation();

        foreach ($aOperations as $iKey => $sOperation) {
            if (mb_strpos($sExp, $aOperations[$iKey])) {
                return $sOperation;
            }
        }

        return false;
    }

    /**
     * Выполняет математическую операцию $sOperation с учетом размерности с операндами $firstOperand и $lastOperand.
     *
     * @param $sOperation
     * @param $firstOperand
     * @param $lastOperand
     *
     * @throws Exception
     *
     * @return string
     */
    public function doMathOperation($sOperation, $firstOperand, $lastOperand)
    {
        // разбор операндов на число-размерность
        $firstOperandDimension = $this->findDimension($firstOperand);
        $firstOperand = mb_substr($firstOperand, 0, mb_strlen($firstOperand) - mb_strlen($firstOperandDimension));
        $lastOperandDimension = $this->findDimension($lastOperand);
        $lastOperand = mb_substr($lastOperand, 0, mb_strlen($lastOperand) - mb_strlen($lastOperandDimension));

        // счераем размерность результата
        if ($firstOperandDimension == $lastOperandDimension) {
            $sResultDimension = $firstOperandDimension;
        } elseif (!$firstOperandDimension) {
            $sResultDimension = $lastOperandDimension;
        } elseif (!$lastOperandDimension) {
            $sResultDimension = $firstOperandDimension;
        } else {
            throw new Exception('CSS Parser ERROR: incorrect input vars!');
        }

        // сделано для предотвращения поломок на строках типа "'.cke_browser_gecko * {'" (есть 2 вхождения)
        if (!is_numeric($firstOperand) && !is_numeric($lastOperand)) {
            throw new Exception('CSS Parser ERROR: incorrect input vars!');
        }
        // считаем числовой результат
        switch ($sOperation) {
            case ' + ':
                $sExpression = $firstOperand + $lastOperand;
                break;
            case ' * ':
                $sExpression = $firstOperand * $lastOperand;
                break;
            case ' - ':
                $sExpression = $firstOperand - $lastOperand;
                break;
            case ' / ':
                $sExpression = $firstOperand / $lastOperand;
                break;
            default:
                throw new Exception('CSS Parser ERROR: incorrect input vars!');
        }

        if ($sResultDimension == 'px') {
            $sExpression = (int) $sExpression;
        }

        return $sExpression . $sResultDimension;
    }

    /**
     * Поиск размерности в операнде $sOperand.
     *
     * @param $sOperand
     *
     * @return string
     */
    public function findDimension($sOperand)
    {
        if (mb_strpos($sOperand, 'em')) {
            return 'em';
        }
        if (mb_strpos($sOperand, 'px')) {
            return 'px';
        }
        if (mb_strpos($sOperand, '%')) {
            return '%';
        }

        return '';
    }

    /**
     * Выполняет математическое преобразование в строке $sExpression.
     *
     * @param string $sExpression
     *
     * @return string
     */
    public function calcMathExpressing($sExpression)
    {
        if ($sOperation = $this->findMathOperation($sExpression)) {
            $iOperationPos = mb_strpos($sExpression, $sOperation); // получаем позицию операции

            // ищем начало выражение
            $iBeginExpression = $iOperationPos;
            while ($iBeginExpression > 0) {
                if ($sExpression[$iBeginExpression - 1] == ' ') {
                    break;
                }
                --$iBeginExpression;
            }

            // ищем конец выражения
            $iEndExpression = $iOperationPos + 2;
            while ($iEndExpression < (mb_strlen($sExpression) - 2)) {
                if (
                    $sExpression[$iEndExpression + 1] == ' ' ||
                    $sExpression[$iEndExpression + 1] == ';'
                ) {
                    break;
                }
                ++$iEndExpression;
            }

            $sCurResult = $this->doMathOperation(
                $sOperation,
                mb_substr($sExpression, $iBeginExpression, $iOperationPos - $iBeginExpression),
                mb_substr($sExpression, $iOperationPos + 3, $iEndExpression - $iOperationPos - 2)
            );

            if ($sCurResult) {
                $sNewExpression = mb_substr($sExpression, 0, $iBeginExpression) . $sCurResult . mb_substr($sExpression, $iEndExpression + 1);

                $sExpression = $sNewExpression;
            }
        }

        return $sExpression;
    }

    /**
     * Поиск и выполнение в строке $sExp математических преобразований.
     *
     * @param string $sExp
     *
     * @return string
     */
    public function calcMathString($sExp)
    {
        $sOldExp = $sExp;
        try {
            $k = 0;
            while ($this->isMathString($sExp)) {
                $sExp = $this->calcMathExpressing($sExp);

                ++$k;

                if ($k > 30) {
                    break;
                } // ограничение на бесконечный цикл
            }
        } catch (Exception $e) {
            // сделано для предотвращения поломок на строках типа "'.cke_browser_gecko * {'" (есть 2 вхождения)
            // в идеале переделать на нормальный выброс ошибки, а try убрать
            $sExp = $sOldExp;
        }

        return $sExp;
    }

    /**
     * Метод парсинга конкретного файла.
     *
     * @param $sFileName
     *
     * @throws Exception
     *
     * @return string
     */
    public function parseFile($sFileName)
    {
        $sMergedFile = '';
        $sCurrentLayer = '';
        $sCurrentGroup = '';

        // Открываем файл
        if (($rFile = fopen($sFileName, 'r')) === false) {
            throw new Exception('Невозможно открыть файл ' . $sFileName . ' для чтения!');
        }
        while (!feof($rFile)) {
            // Читаем файл по строкам
            $sFileLine = fgets($rFile);

            // Проверяем на совпадения
            preg_match_all('/\/\*{1}\s*(?<command>layer|group|param|const)\:(?<content>.*)\*\/{1}/xUi', $sFileLine, $aMatches);

            if (count($aMatches['command'])) {
                //Интерпретация комманды

                // Разбиваем по |, обрезаем пробелы
                $aLineParts = explode('|', $aMatches['content'][0]);
                array_walk($aLineParts, static function (&$value) { $value = trim($value); });

                switch ($aMatches['command'][0]) {
                    case 'layer':

                        // Установить текущий слой
                        $sCurrentLayer = $aLineParts[0];
                    break;

                    case 'group':

                        // Установить текущую группу
                        $sCurrentGroup = $aLineParts[0];
                    break;
                }

                // Добавить к выходной переменной
                //$sMergedFile.= $sFileLine;
            } // if
            else {
                //Парсинг параметров в строке

                $iOffset = 0;

                $sCurrentLine = '';

                // Цикл по строке
                while (($iCommandStart = mb_strpos($sFileLine, '[', $iOffset)) !== false) {
                    // Если найдена и открывающая, и закрывающая квадратные скобки
                    if ($iCommandEnd = mb_strpos($sFileLine, ']', $iCommandStart)) {
                        // Добавляем в выходную переменную все, что до открывающей скобки
                        $sCurrentLine .= mb_substr($sFileLine, $iOffset, $iCommandStart - $iOffset);

                        // Вырезаем выражение для подстановки
                        $sParamExpression = mb_substr($sFileLine, $iCommandStart + 1, $iCommandEnd - $iCommandStart - 1);

                        // Ищем значение выражения в массиве параметров и подставляем его в выходную переменную
                        if (($iPoint = mb_strrpos($sParamExpression, '.')) !== false) {
                            // Если в выражении есть точка, значит это глобальный параметр - проверяем его наличие в массиве параметров
                            if (isset($this->aParams[$sCurrentLayer][$sParamExpression])) {
                                // если нашли - заменяем значение
                                $sParamExpression = $this->aParams[$sCurrentLayer][$sParamExpression]['value'];
                            } // if
                            else {
                                // проверям на глобальный параметр с указанием слоя
                                if (mb_strrpos($sParamExpression, '..')) {
                                    list($sTmpLayer, $sTmpPath) = explode('..', $sParamExpression, 2);
                                    if (isset($this->aParams[$sTmpLayer][$sTmpPath])) {
                                        // если нашли - заменяем значение
                                        $sParamExpression = $this->aParams[$sTmpLayer][$sTmpPath]['value'];
                                    }
                                } // if
                                else {
                                    // если нигде ненашли - возвращаем все как было
                                    $sParamExpression = '[' . $sParamExpression . ']';
                                } // else
                            } // else

                            if ($sParamExpression === 'empty') {
                                $sParamExpression = '';
                            }
                        } // if
                        else {
                            $sParamExpression = (isset($this->aParams[$sCurrentLayer][$sCurrentGroup . '.' . $sParamExpression])) ? $this->aParams[$sCurrentLayer][$sCurrentGroup . '.' . $sParamExpression]['value'] : "[{$sParamExpression}]";
                        }

                        // добавляем результат выражения в скобках
                        $sCurrentLine .= $sParamExpression;
                        $iOffset = $iCommandEnd + 1;
                    } else {
                        break;
                    }
                } // while

                // Добавляем все, что лежит между закрывающей скобкой и концом строки
                $sCurrentLine .= mb_substr($sFileLine, $iOffset, mb_strlen($sFileLine) - $iOffset);

                // выполняем математику в выражении
                $sCurrentLine = trim($sCurrentLine);
                $sCurrentLine = $this->calcMathString($sCurrentLine);

                $sMergedFile .= $sCurrentLine;
            } // else
        }

        return $sMergedFile;
    }

    //function parseFile()

    public function clearCSSCache($sDirName)
    {
        if (!is_dir($sDirName)) {
            return false;
        }

        /** @var \Directory $aEntry */
        $aEntry = dir($sDirName);

        /* @noinspection PhpUndefinedFieldInspection */
        if ($aEntry->handle) {
            while (false !== ($entry = $aEntry->read())) {
                if ($entry != '.' and $entry != '..' and !is_dir($entry)) {
                    unlink($sDirName . $entry);
                }
            }
        }
        $aEntry->close();

        return true;
    }

    /**
     * Собирает набор css файлов, разбивает их по группам, сортирует
     * и отдает в виде структурированного массива.
     *
     * @param array $aInputFiles набор css файлов с описанием
     *
     * @return array|bool
     */
    public function rebuildCSSArray($aInputFiles)
    {
        if ($aInputFiles) {
            $aTempCSSFiles = [];

            foreach ($aInputFiles as $aFileGroup) {
                foreach ($aFileGroup as $sFileKey => $sFile) {
                    if (is_array($sFile)) {
                        $sCurrentLayer = (isset($sFile['layer'])) ? $sFile['layer'] : Design::versionDefault;
                        $sCurrentCondition = (isset($sFile['condition'])) ? $sFile['condition'] : 'default';
                        $iWeight = (isset($sFile['weight'])) ? $sFile['weight'] : $this->iDefaultWeight;
                        $sCompiledPath = str_replace('/skewer/build/', BUILDPATH, $sFileKey);

                        $aTempCSSFiles[] = [
                            'path' => $sFileKey,
                            'layer' => $sCurrentLayer,
                            'condition' => $sCurrentCondition,
                            'weight' => $iWeight,
                            'compiledPath' => $sCompiledPath,
                        ];
                    } else {
                        $sCompiledPath = str_replace('/skewer/build/', BUILDPATH, $sFile);

                        $aTempCSSFiles[] = [
                            'path' => $sFile,
                            'layer' => Design::versionDefault,
                            'condition' => 'default',
                            'weight' => $this->iDefaultWeight,
                            'compiledPath' => $sCompiledPath,
                        ];
                    }
                }
            }

            // сортировка по весам
            usort($aTempCSSFiles, static function ($a, $b) {
                return $b['weight'] - $a['weight'];
            });

            $aCSSFiles = [];

            if (count($aTempCSSFiles)) {
                foreach ($aTempCSSFiles as $aFile) {
                    $aCSSFiles[$aFile['layer']][$aFile['condition']][] = $aFile['compiledPath'];
                }
            }

            return $aCSSFiles;
        }

        return false;
    }

    public function updateDesignSettings()
    {
        $oDesignManager = new DesignManager();
        $oDesignManager->updateDesignSettings(['groups' => $this->aGroups, 'params' => $this->aParams]);
        $oDesignManager->saveReferences($this->aReferences);
        \Yii::$app->clearAssets();
    }
}
