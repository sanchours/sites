<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 12.09.2018
 * Time: 16:05.
 */

namespace skewer\components\cleanup;

abstract class CleanupPrototype
{
    private $aSpecialDirectories;

    //должен вернуть массив данных
    //конкретной структуры
    abstract public function getData();

    public static function className()
    {
        return get_called_class();
    }

    public function getFormatDataScanDb()
    {
        return [
            'module' => '',
            'action' => 'scanDb',
            'file' => '',
            'assoc_data_storage' => '',
        ];
    }

    public function getFormatDataScanFiles()
    {
        return [
            'module' => '',
            'action' => 'scanFiles',
            'file' => '',
            'assoc_data_storage' => '',
        ];
    }

    public function clearDoubleSlach($string)
    {
        return str_replace('//', '/', $string);
    }

    abstract public function scanFiles();

    /**
     * @return mixed
     */
    public function getSpecialDirectories()
    {
        return $this->aSpecialDirectories;
    }

    /**
     * @param mixed $aSpecialDirectories
     */
    public function setSpecialDirectories(array $aSpecialDirectories)
    {
        if (!is_array($aSpecialDirectories)) {
            $aSpecialDirectories = [];
        }

        $this->aSpecialDirectories = $aSpecialDirectories;
    }
}
