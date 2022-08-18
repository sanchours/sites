<?php

namespace unit\components\garbage;

use skewer\base\queue;
use skewer\components\config\installer\Api;
use skewer\components\garbage\DeleteOldFilesTask;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\Task;
use yii\helpers\FileHelper;

/**
 * codecept run codeception/unit/components/garbage/DeleteOldFilesTest.php
 * Class DeleteOldFilesTest.
 */
class DeleteOldFilesTest extends \Codeception\Test\Unit
{
    /*Сколько раз запустить импорт*/
    private $iIterations = 10;

    /*
     * Сколько архивов состарить
     */
    private $iSetOld = 5;

    private $iCountPerIteration = 2;

    protected function setUp()
    {
        $oInstaller = new Api();

        if (!$oInstaller->isInstalled('Import', 'Tool')) {
            $oInstaller->install('Import', 'Tool');
        }
    }

    /**
     * @covers \skewer\components\garbage\DeleteOldFilesTask
     */
    public function testExecute()
    {
        /*Создали папку*/
        if (!file_exists(PRIVATE_FILEPATH . 'garbage')) {
            mkdir(PRIVATE_FILEPATH . 'garbage');
        }

        /*Удалим все ее содержимое*/
        FileHelper::removeDirectory(PRIVATE_FILEPATH . 'garbage');

        \Yii::$app->db->createCommand("
        INSERT INTO `import_template` (`id`, `title`, `card`, `coding`, `type`, `source`, `provider_type`, `settings`, `use_dict_cache`, `use_goods_hash`) VALUES
          (3, 'ttest', 'group1', 'utf-8', 1, '/vsite.csv', 1, '{\"id\":\"1\",\"title\":\"ttest\",\"card\":\"group1\",\"coding\":\"utf-8\",\"type\":\"1\",\"source\":\"\\/files\\/110\\/vsite.csv\",\"provider_type\":\"1\",\"use_dict_cache\":\"0\",\"use_goods_hash\":\"0\",\"fields\":{\"section\":{\"name\":\"section\",\"importFields\":\"5\",\"type\":\"Section\",\"params\":{\"delimiter\":\";\",\"delimiter_path\":\"\\/\",\"baseId\":70,\"defImportSectionId\":0,\"create\":1,\"template\":254}},\"title\":{\"name\":\"title\",\"importFields\":\"1\",\"type\":\"Title\"},\"article\":{\"name\":\"article\",\"importFields\":\"0\",\"type\":\"Unique\",\"params\":{\"create\":1}},\"alias\":{\"name\":\"alias\",\"importFields\":\"\",\"type\":0},\"gallery\":{\"name\":\"gallery\",\"importFields\":\"\",\"type\":0},\"announce\":{\"name\":\"announce\",\"importFields\":\"\",\"type\":0},\"obj_description\":{\"name\":\"obj_description\",\"importFields\":\"\",\"type\":0},\"old_price\":{\"name\":\"old_price\",\"importFields\":\"\",\"type\":0},\"price\":{\"name\":\"price\",\"importFields\":\"4\",\"type\":\"Money\"},\"measure\":{\"name\":\"measure\",\"importFields\":\"\",\"type\":0},\"active\":{\"name\":\"active\",\"importFields\":\"\",\"type\":0},\"buy\":{\"name\":\"buy\",\"importFields\":\"\",\"type\":0},\"countbuy\":{\"name\":\"countbuy\",\"importFields\":\"\",\"type\":0},\"fastbuy\":{\"name\":\"fastbuy\",\"importFields\":\"\",\"type\":0},\"on_main\":{\"name\":\"on_main\",\"importFields\":\"\",\"type\":0},\"hit\":{\"name\":\"hit\",\"importFields\":\"\",\"type\":0},\"new\":{\"name\":\"new\",\"importFields\":\"\",\"type\":0},\"discount\":{\"name\":\"discount\",\"importFields\":\"\",\"type\":0},\"proizvoditel\":{\"name\":\"proizvoditel\",\"importFields\":\"\",\"type\":0}}}', 0, 0);
        ")->execute();

        copy(__DIR__ . '/vsite.csv', WEBPATH . 'vsite.csv');

        $iTaskId = 0;

        /** @var ImportTemplateRow $oImportTemplate */
        $oImportTemplate = ImportTemplate::findOne(['id' => 3]);

        defined('TestTask') or define('TestTask', 1);

        /*Запустим импорт несколько раз*/
        for ($i = 1; $i <= $this->iIterations; ++$i) {
            /*Очистим задачи*/
            \Yii::$app->db->createCommand('DELETE FROM `task`')->execute();

            /* Запуск импорта */
            queue\Task::runTask(Task::getConfigTask($oImportTemplate), $iTaskId);

            $iTaskId = 0;
        }

        /*Получим список директорий с бекапами файлов.*/
        $aFiles = $this->getBackupFiles(PRIVATE_FILEPATH . 'garbage');

        /*Запустим удаление*/
        $oDeleteFiles = new DeleteOldFilesTask();

        $oDeleteFiles->setCountFilesPerIteration($this->iCountPerIteration);

        $oDeleteFiles->execute();

        $aFiles2 = $this->getBackupFiles(PRIVATE_FILEPATH . 'garbage');

        $this->assertEquals($aFiles, $aFiles2, 'Произошло удаление НЕ устаревших файлов');

        /*Ничего не удалилось.*/
        /*Теперь поменяем даты некоторым папкам*/
        for ($i = 0; $i <= $this->iSetOld; ++$i) {
            $iSecond = str_pad($i, 2, '0', STR_PAD_LEFT);
            $sDate = date('Y-m-d-H-i-', time() - (31 * (24 * 60 * 60))) . $iSecond;

            if (isset($aFiles2[$i])) {
                rename(str_replace('vsite.csv', '', $aFiles2[$i]), PRIVATE_FILEPATH . 'garbage/' . $sDate . '/');
                unset($aFiles2[$i]);
            }
        }

        $aFiles3 = $this->getBackupFiles(PRIVATE_FILEPATH . 'garbage');

        /*Будем эмулировать запуски крона пока не удалим все что сделали устаревшим*/
        $iRunCron = ceil($this->iCountPerIteration / $this->iSetOld + 1);

        for ($i = 0; $i <= $iRunCron; ++$i) {
            $oDeleteFiles->execute();

            $aFiles3 = $this->getBackupFiles(PRIVATE_FILEPATH . 'garbage');

            $aFiles2 = array_values($aFiles2);
        }

        $this->assertEquals($aFiles2, $aFiles3, 'Не произошло удаление старого файла');

        unlink(WEBPATH . 'vsite.csv');
    }

    private function getBackupFiles($dir)
    {
        $aFiles = [];

        $files = glob($dir . '/*');
        count($files);
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (file_exists($file . '/vsite.csv')) {
                    $aFiles[] = $file . '/vsite.csv';
                }
            }
        }

        return $aFiles;
    }
}
