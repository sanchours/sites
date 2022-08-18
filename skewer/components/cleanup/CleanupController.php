<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 12.09.2018
 * Time: 14:03.
 */

namespace skewer\components\cleanup;

class CleanupController
{
    /** @var CleanupHub */
    public $oCleanupHub;

    /**
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->oCleanupHub = new CleanupHub();

        CleanupHelper::printMessage('init cleanup process');
        $this->oCleanupHub->cleanTable();
    }

    public function execute()
    {
        //этап сканирования
        CleanupHelper::printMessage('start data scanning');
        $this->oCleanupHub->scanData();
        CleanupHelper::printMessage('end data scanning');
        //этап анализа
        CleanupHelper::printMessage('data analysis start');
        $this->analyze();
        CleanupHelper::printMessage('data analysis end');
        //этап оповещения
        $this->notificationUser();
    }

    private function analyze()
    {
        CleanupHelper::printMessage('start adding data for analysis');
        $this->oCleanupHub->addGroupedData();
        CleanupHelper::printMessage('end of adding data for analysis');

        CleanupHelper::printMessage('start cleaning the site from files');
        $this->oCleanupHub->deleteFilesFromSite();
        CleanupHelper::printMessage('end cleaning the site from files');
    }

    private function notificationUser()
    {
        $this->oCleanupHub->dataAggregation();

        $sMessage = $this->oCleanupHub->getLogger()->getMessage();

        CleanupHelper::printMessage($sMessage);

        \skewer\base\log\Logger::dump($sMessage);

        CleanupHelper::printMessage('Report can be viewed in the logs');
        CleanupHelper::printMessage('');
    }

    /**
     * @throws \yii\db\Exception
     */
    public function complete()
    {
        $this->oCleanupHub->cleanTable();
        CleanupHelper::printMessage('complete cleanup process');
    }
}
