<?php
/**
 * Created by PhpStorm.
 * User: ermak
 * Date: 09.08.2017
 * Time: 11:52.
 */

namespace skewer\components\import\field;

use skewer\base\log\Logger;
use skewer\base\SysVar;
use skewer\build\Catalog\Goods;
use skewer\build\Cms\FileBrowser\Api;
use skewer\helpers\Files;
use yii\base\ErrorException;
use yii\helpers\FileHelper;

/**
 * Обработчик поля типа файл.
 */
class FileLink extends Prototype
{
    protected $allowedFormatsFile = [];

    const FolderImportFiles = 'fileLink/';
    //Флаг указывающий, что прошли проверку.
    protected $bCheckFormat = false;

    protected $timeout = 5;

    protected static $parameters = [
        'type_file' => [
            'title' => 'field_section_delimiter',
            'datatype' => 's',
            'viewtype' => 'show',
            'default' => '',
        ],
    ];

    public function init()
    {
        try {
            $this->allowedFormatsFile = explode(', ', SysVar::get('import_upload_formats'));
        } catch (\Exception $e) {
            Logger::dump(\Yii::t('import', 'format_settings_error'));
            Logger::dump($e->getMessage());
        }
    }

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @throws \Exception
     * @throws \yii\base\Exception
     *
     * @return mixed|string
     */
    public function getValue()
    {
        //Проверим расширения, если хоть один неверный, то вернём пустую строку
        foreach ($this->values as $k => $value) {
            $ext = mb_strtolower(mb_substr($value, mb_strrpos($value, '.') + 1));
            if (!in_array($ext, $this->allowedFormatsFile)) {
                return '';
            }
            $sNameFile = basename($value);
            $sDirPath = IMPORT_FILEPATH . self::FolderImportFiles;
            if (!is_dir($sDirPath)) {
                $bRes = FileHelper::createDirectory($sDirPath);
                if (!$bRes) {
                    throw new \Exception('no create directory for images or file.');
                }
            }

            if ($this->loadFile($value, $sNameFile)) {
                $this->values[$k] = $sDirPath . $sNameFile;
            } else {
                return '';
            }
        }

        $this->bCheckFormat = true;
        //Отдадим что нибудь, все равно еще нужно узнать куда сохранилось прежде чем перемещать файлы
        return implode(',', $this->values);
    }

    /**
     * Загрузка файла из источника.
     *
     * @param $sFile
     * @param $sName
     *
     * @return int
     */
    private function loadFile($sFile, $sName)
    {
        $load = 0;
        $curl = curl_init($sFile);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);
        if ($info['http_code'] != '200') {
            Logger::dump('Возникла ошибка при запросе файла.');
            Logger::dump('URL: ' . $info['url']);
            Logger::dump('Http код: ' . $info['http_code']);
            Logger::dump('Content Type: ' . $info['content_type']);
            Logger::dump('Error: ' . $error);
            $this->logger->setListParam(
                'error_list',
                'Файл не был загружен! ' . $sFile . ' Причина: http-code ' . $info['http_code'] . ', error: ' . $error
            );
        } else {
            $sDirPath = IMPORT_FILEPATH . self::FolderImportFiles;
            if ($content) {
                curl_close($curl);
                try {
                    if (file_exists($sDirPath . $sName)) :
                        unlink($sDirPath . $sName);
                    endif;
                    $fp = fopen($sDirPath . $sName, 'x');
                    fwrite($fp, $content);
                    fclose($fp);
                    $load = 1;
                } catch (ErrorException $e) {
                    Logger::dumpException($e);
                    $this->logger->setListParam(
                        'error_list',
                        'Файл не был загружен! Возникла ошибка при записи файла. ' . $sFile
                    );
                    $load = 0;

                    return $load;
                }
            } else {
                $this->logger->setListParam(
                    'error_list',
                    'Возникла ошибка при получении файла.'
                );
                $load = 0;
            }
        }

        return $load;
    }

    /**
     * Создание папки и перемещение файла в files.
     *
     * @throws \Exception
     */
    public function afterSave()
    {
        if (!$this->bCheckFormat) {
            return;
        }

        $aFiles = [];

        $oGoodsRow = $this->getGoodsRow();
        if (!$oGoodsRow) {
            return;
        }

        $iIdSectionPath = Api::getSectionIdbyAlias(Api::getAliasByModule(Goods\Module::className()));
        $sNewPath = Files::createFolderPath($iIdSectionPath) . \DIRECTORY_SEPARATOR;
        $bRes = false;
        foreach ($this->values as $file) {
            $fileName = basename($file);

            //Удалим старый только, если оба существуют
            if (file_exists(IMPORT_FILEPATH . self::FolderImportFiles . $fileName) and file_exists($sNewPath . $fileName)) {
                unlink($sNewPath . $fileName);
            }
            try {
                $bRes = copy(IMPORT_FILEPATH . self::FolderImportFiles . $fileName, $sNewPath . $fileName);
            } catch (\Exception $e) {
                $this->logger->setListParam(
                    'error_list',
                    'Файл не загружен! Ошибка чтении файла. Проверьте существование и права доступа файла ' . $file
                );
                Logger::dumpException($e->getMessage());
            }
            if (!$bRes) {
                break;
            }
            //Обрежим лишние, оставим только начиная с /files
            $aFiles[] = mb_substr($sNewPath . $fileName, mb_strpos($sNewPath . $fileName, '/files/' . $iIdSectionPath . '/' . $fileName));
        }

        if ($bRes) {
            $oGoodsRow->setData([$this->fieldName => implode(',', $aFiles)]);
            $oGoodsRow->save();
        }
    }
}
