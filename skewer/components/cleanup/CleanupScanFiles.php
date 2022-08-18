<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 28.09.2018
 * Time: 14:43.
 */

namespace skewer\components\cleanup;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * class для сканирования файлов системы
 * API.
 */
class CleanupScanFiles
{
    /** @var array $aSpecialDirectories */
    public $aSpecialDirectories;

    public function scanFiles(CleanupPrototype $oCleanup)
    {
        $aData = $oCleanup->scanFiles();

        return $aData;
    }

    /**
     * @param RecursiveDirectoryIterator $oDirectoryIterator
     *
     * @return array
     */
    public static function recursiveScanFiles($oDirectoryIterator)
    {
        $aData = [];
        if ($oDirectoryIterator->valid()) {
            do {
                if ($oDirectoryIterator->hasChildren()) {
                    $aResultsScan = self::recursiveScanFiles($oDirectoryIterator->getChildren());
                    $aData = array_merge($aData, $aResultsScan);
                } else {
                    $aData[] = $oDirectoryIterator->getPathname();
                }
                $oDirectoryIterator->next();
            } while ($oDirectoryIterator->valid());
        }

        return $aData;
    }
}
