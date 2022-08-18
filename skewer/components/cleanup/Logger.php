<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 01.11.2018
 * Time: 14:08.
 */

namespace skewer\components\cleanup;

class Logger
{
    private $aStorage = [];

    /**
     * Структура массива
     * [countAllScanRecord => (int)<value>,
     * countDeleteFilesAnalyze => (int)<value>,
     * errorDeleteFile => [(string)<value>],
     * countAllFilesAnalyze=>(int)<value>,
     * countCorrectFilesAnalyze=>(int)<value>,
     * countLinkDbAnalyze=>(int)<value>.
     *
     *
     * ]
     */
    private $aCleanupData = [];

    /**
     * Установка параметра1.
     *
     * @param $name
     * @param $value
     */
    public function setParam($name, $value)
    {
        $this->aStorage[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return bool|mixed
     */
    public function getParam($name)
    {
        if (!isset($this->aStorage[$name])) {
            return false;
        }

        return $this->aStorage[$name];
    }

    public function getStorage()
    {
        return $this->aStorage;
    }

    /**
     * Сохранение логов.
     */
    public function save()
    {
        //пока что только из aStorage
    }

    /**
     * Установка параметра.
     *
     * @param $name
     * @param $value
     */
    public function setParamCleanup($name, $value)
    {
        $this->aCleanupData[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return bool|mixed
     */
    public function getParamCleanup($name)
    {
        if (!isset($this->aCleanupData[$name])) {
            return false;
        }

        return $this->aCleanupData[$name];
    }

    /**
     * Добавление значения в список.
     *
     * @param $name
     * @param $value
     */
    public function setListParamCleanup($name, $value)
    {
        if (!isset($this->aStorage[$name])) {
            $this->aStorage[$name] = [];
        }

        if (!is_array($this->aStorage[$name])) {
            $this->aStorage[$name] = [$this->aStorage[$name]];
        }

        $this->aStorage[$name][] = $value;
    }

    public function getCleanupData()
    {
        return $this->aCleanupData;
    }

    public function getMessage()
    {
        $aData = $this->getCleanupData();
        $sMessage = 'Report ' . date('Y-m-d') . CleanupHelper::addNEL();

        if (isset($aData['countAllScanRecord'])) {
            $sMessage .= 'Total processed data ' . $aData['countAllScanRecord'] . CleanupHelper::addNEL();
        }

        if (isset($aData['countAllFilesAnalyze'])) {
            $sMessage .= 'Total files in processing ' . $aData['countAllFilesAnalyze'] . CleanupHelper::addNEL();
        }

        if (isset($aData['countCorrectFilesAnalyze'])) {
            $sMessage .= 'Count of correct files ' . $aData['countCorrectFilesAnalyze'] . CleanupHelper::addNEL();
        }

        if (isset($aData['countDeleteFilesAnalyze'])) {
            $sMessage .= 'Count of files not listed in the database ' . $aData['countDeleteFilesAnalyze'] . CleanupHelper::addNEL();

            if (isset($aData['errorDeleteFile']) && count($aData['errorDeleteFile'])) {
                $count = count($aData['errorDeleteFile']);
                $sMessage .= 'Deleted ' . ((int) $aData['countDeleteFilesAnalyze'] - $count) . CleanupHelper::addNEL();
                $sMessage .= 'Not deleted ' . $count . CleanupHelper::addNEL();
                $sMessage .= 'Errors can be viewed in error log' . CleanupHelper::addNEL();
            } else {
                $sMessage .= 'Deleted ' . $aData['countDeleteFilesAnalyze'] . CleanupHelper::addNEL();
            }
            CleanupHelper::printMessage(CleanupHelper::addNEL());
        }

        if (isset($aData['countLinkDbAnalyze'])) {
            $sMessage .= 'Count of links in the database without matching files ' . $aData['countLinkDbAnalyze'] . CleanupHelper::addNEL();
        }

        return $sMessage;
    }
}
