<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 13.09.2018
 * Time: 10:47.
 */

namespace skewer\build\Tool\Import;

use skewer\base\section\models\TreeSection;
use skewer\components\cleanup\CleanupPrototype;
use skewer\components\cleanup\CleanupScanFiles;
use skewer\components\import\Api;
use skewer\components\import\ar\ImportTemplate;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class Cleanup extends CleanupPrototype
{
    public function getData()
    {
        $aData = $this->scanDb();

        return $aData;
    }

    private function scanDb()
    {
        $aImportTemplates = ImportTemplate::find()
            ->asArray()
            ->getAll();

        $aData = [];

        foreach ($aImportTemplates as $aImportTemplate) {
            if ($aImportTemplate['type'] != Api::Type_Url) {
                $file = 'web' . $this->unificationPath($aImportTemplate['source']);
                $aData[] = $this->formatDataScanDb($file, $aImportTemplate);
            } else {
                $aSettings = json_decode($aImportTemplate['settings'], true);
                if (isset($aSettings['file'])) {
                    $aData[] = $this->formatDataScanDb($this->unificationPath($aSettings['file']), $aImportTemplate);
                }
            }
        }

        return $aData;
    }

    /**
     * @param $file
     * @param array $aTemplate
     *
     * @return array|bool
     */
    public function formatDataScanDb($file, $aTemplate)
    {
        if (empty($file)) {
            return false;
        }

        $aData = $this->getFormatDataScanDb();
        $aData['module'] = self::className();
        $aData['file'] = $this->clearDoubleSlach($file);
        $aData['assoc_data_storage'] = json_encode([
            'title' => $aTemplate['title'],
            'card' => $aTemplate['card'],
            'type' => $aTemplate['type'],
            'template_id' => $aTemplate['id'],
            'source' => $aTemplate['source'],
            'settings' => $aTemplate['settings'],
        ]);

        return $aData;
    }

    public function checkFormat()
    {
    }

    private function unificationPath($path)
    {
        $path = str_replace(ROOTPATH, '', $path);

        return $path;
    }

    /**
     * @param string $file
     * @param string $album
     *
     * @return array|bool
     */
    public function formatDataScanFiles($file)
    {
        if (empty($file)) {
            return false;
        }

        $aData = $this->getFormatDataScanFiles();
        $aData['module'] = self::className();
        $aData['file'] = $this->clearDoubleSlach(
            $this->unificationPath($file)
        );

        return $aData;
    }

    /**
     * @param RecursiveDirectoryIterator $oDirectoryIterator
     *
     * @return array
     */
    private function scanFilesImportDirectory($oDirectoryIterator)
    {
        $aData = [];
        $aDirectory = $this->getSpecialDirectories();

        foreach ($aDirectory as &$item) {
            $item = str_replace('/', '', $item);
        }

        if ($oDirectoryIterator->valid()) {
            do {
                if (!in_array($oDirectoryIterator->getFilename(), $aDirectory)) {
                    $aResultsScan = CleanupScanFiles::recursiveScanFiles($oDirectoryIterator->getChildren());
                    $aData = array_merge($aData, $aResultsScan);
                }
                $oDirectoryIterator->next();
            } while ($oDirectoryIterator->valid());
        }

        return $aData;
    }

    /**
     * @return array
     */
    public function scanFiles()
    {
        // проверяем папку import
        $oDirectoryIterator = new RecursiveDirectoryIterator(IMPORT_FILEPATH, RecursiveDirectoryIterator::SKIP_DOTS);

        $aFilesData = $this->scanFilesImportDirectory($oDirectoryIterator);
        $aData = [];

        foreach ($aFilesData as $item) {
            $aData[] = $this->formatDataScanFiles($item);
        }

        //проверяем папки private_files и files
        $sLibAlias = \skewer\build\Cms\FileBrowser\Api::getAliasByModule(Module::className());
        $aLib = TreeSection::find()->where(['alias' => $sLibAlias, 'type' => 1])->asArray()->one();
        $iIdLib = $aLib['id'];

        if (file_exists(PRIVATE_FILEPATH . $iIdLib)) {
            $oDirectoryIterator = new RecursiveDirectoryIterator(PRIVATE_FILEPATH . $iIdLib, RecursiveDirectoryIterator::SKIP_DOTS);

            $aFilesData = CleanupScanFiles::recursiveScanFiles($oDirectoryIterator);
            foreach ($aFilesData as $item) {
                $aData[] = $this->formatDataScanFiles($item);
            }
        }

        if (file_exists(FILEPATH . $iIdLib)) {
            $oDirectoryIterator = new RecursiveDirectoryIterator(FILEPATH . $iIdLib, RecursiveDirectoryIterator::SKIP_DOTS);

            $aFilesData = CleanupScanFiles::recursiveScanFiles($oDirectoryIterator);
            foreach ($aFilesData as $item) {
                $aData[] = $this->formatDataScanFiles($item);
            }
        }

        if (file_exists(FILEPATH . 'import_sources')) {
            $oDirectoryIterator = new RecursiveDirectoryIterator(FILEPATH . 'import_sources', RecursiveDirectoryIterator::SKIP_DOTS);

            $aFilesData = CleanupScanFiles::recursiveScanFiles($oDirectoryIterator);
            foreach ($aFilesData as $item) {
                $aData[] = $this->formatDataScanFiles($item);
            }
        }

        return $aData;
    }
}
