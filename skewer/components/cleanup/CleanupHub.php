<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 30.10.2018
 * Time: 14:18.
 */

namespace skewer\components\cleanup;

class CleanupHub
{
    const LIMIT_COUNT = 50000;

    private $aCleanup;

    /** @var CleanupRepository */
    private $oCleanupRepository;

    /** @var Logger */
    private $logger;

    /**
     * CleanupHub constructor.
     *
     * @throws \skewer\components\config\Exception
     */
    public function __construct()
    {
        $this->oCleanupRepository = CleanupRepository::getInstance();
        $this->aCleanup = \Yii::$app->register->getAllCleanup();
        $this->logger = new Logger();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function cleanTable()
    {
        $this->oCleanupRepository->clearScanResultsTable();
        $this->oCleanupRepository->clearAnalyzeTable();
    }

    public function scanData()
    {
        if (isset($this->aCleanup['scanDb'])) {
            foreach ($this->aCleanup['scanDb'] as $sCleanup) {
                /** @var \skewer\components\cleanup\CleanupPrototype $oCleanup */
                $oCleanup = new $sCleanup();
                $aData = $oCleanup->getData();

                $this->oCleanupRepository->saveScanResults($aData);

                if (isset($this->aCleanup['specialDirectories'][$sCleanup])) {
                    $oCleaScanFiles = new CleanupScanFiles();
                    $oCleaScanFiles->aSpecialDirectories = $this->aCleanup['specialDirectories'][$sCleanup];
                    $oCleanup->setSpecialDirectories($this->aCleanup['specialDirectories'][$sCleanup]);
                    $aData = $oCleaScanFiles->scanFiles($oCleanup);
                    $this->oCleanupRepository->saveScanResults($aData);
                }
            }
        }
    }

    public function addGroupedData()
    {
        $offset = 0;
        $currentItem = $this->getFormatAnalyzeData();
        do {
            $aDataLimit = $this->oCleanupRepository->getLimitScanData($offset, self::LIMIT_COUNT);
            $aItems = [];
            if (count($aDataLimit)) {
                foreach ($aDataLimit as $key => $item) {
                    if (empty($currentItem['file'])) {
                        $currentItem['file'] = $item['file'];
                    }
                    if ($currentItem['file'] != $item['file']) {
                        $aItems[] = $currentItem;
                        $currentItem = $this->getFormatAnalyzeData();
                        $currentItem['file'] = $item['file'];
                        $currentItem = $this->handlingDataAnalyze($currentItem, $item);
                    } else {
                        //вызов функции на обработку текущего файла
                        $currentItem = $this->handlingDataAnalyze($currentItem, $item);
                    }
                    if (($key % 100) == 0) {
                        CleanupHelper::printMessage('.', false);
                    }
                }
            }
            $this->saveDataAnalyze($aItems);

            if (count($aDataLimit) < self::LIMIT_COUNT && $offset == 0) {
                $offset = count($aDataLimit);
                CleanupHelper::printMessage(count($aDataLimit));
            } elseif (count($aDataLimit) < self::LIMIT_COUNT && $offset > 0) {
                $offset += count($aDataLimit);
                CleanupHelper::printMessage($offset);
            } else {
                $offset += self::LIMIT_COUNT;
                CleanupHelper::printMessage($offset);
            }
        } while (count($aDataLimit) == self::LIMIT_COUNT);

        $this->logger->setParamCleanup('countAllScanRecord', $offset);

        if ($currentItem['file']) {
            $this->saveDataAnalyze([$currentItem]);
        }
    }

    public function deleteFilesFromSite()
    {
        $offset = 0;
        $countDeleteFiles = $this->oCleanupRepository->getCountFilesAnalyze();
        do {
            $aDataLimit = $this->oCleanupRepository->getLimitFilesDataAnalyze($offset);

            if (count($aDataLimit)) {
                //дропаем в цикле
                foreach ($aDataLimit as $key => $item) {
                    $path = ROOTPATH . $item['file'];
                    try {
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    } catch (\Exception $e) {
                        \skewer\base\log\Logger::dumpException($e);
                        \skewer\base\log\Logger::dump('failed to delete file: ' . $item['file']);
                        $this->logger->setListParamCleanup('errorDeleteFile', $item['file']);
                    } catch (\ErrorException $e) {
                        \skewer\base\log\Logger::dumpException($e);
                        \skewer\base\log\Logger::dump('failed to delete file: ' . $item['file']);
                        $this->logger->setListParamCleanup('errorDeleteFile', $item['file']);
                    }
                }
            }
            $offset += count($aDataLimit);
            CleanupHelper::printMessage($offset . ' files processed');
        } while ($offset < $countDeleteFiles);

        $this->logger->setParamCleanup('countDeleteFilesAnalyze', $offset);
    }

    public function dataAggregation()
    {
        $this->logger->setParamCleanup(
            'countAllFilesAnalyze',
            $this->oCleanupRepository->getCountAllRecordAnalyze()
        );

        $this->logger->setParamCleanup(
            'countCorrectFilesAnalyze',
            $this->oCleanupRepository->getCountCorrectRecordAnalyze()
        );

        $this->logger->setParamCleanup(
            'countLinkDbAnalyze',
            $this->oCleanupRepository->getCountLinkDbAnalyze()
        );
    }

    public function getLogger()
    {
        return $this->logger;
    }

    private function getFormatAnalyzeData()
    {
        return [
            'file' => '',
            'correct' => 0,
            'scanDb' => 0,
            'scanFiles' => 0,
        ];
    }

    private function handlingDataAnalyze($aData, $item)
    {
        if ($aData['correct']) {
            return $aData;
        }

        if ($item['action'] == 'scanDb') {
            $aData['scanDb'] = 1;
        }

        if ($item['action'] == 'scanFiles') {
            $aData['scanFiles'] = 1;
        }

        if ($aData['scanDb'] && $aData['scanFiles']) {
            $aData['correct'] = 1;
        }

        return $aData;
    }

    private function saveDataAnalyze($aData)
    {
        if (empty($aData)) {
            return false;
        }

        return  $this->oCleanupRepository->saveDataAnalyze($aData);
    }
}
