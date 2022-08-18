<?php

namespace skewer\components\import\field;

use skewer\base\log\Logger;
use skewer\base\SysVar;
use skewer\build\Catalog\Goods;
use skewer\build\Cms\FileBrowser\Api;
use skewer\helpers\Files;

/**
 * Обработчик поля типа файл.
 */
class File extends Prototype
{
    protected $allowedFormatsFile = '';

    const FolderImportFiles = 'file/';
    //Флаг указывающий, что прошли проверку.
    protected $bCheckFormat = false;

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
     * @return mixed
     */
    public function getValue()
    {
        //Проверим расширения, если хоть один неверный, то вернём пустую строку
        foreach ($this->values as $value) {
            $ext = mb_strtolower(mb_substr($value, mb_strrpos($value, '.') + 1));
            if (!in_array($ext, $this->allowedFormatsFile)) {
                return '';
            }
        }
        $this->bCheckFormat = true;
        //Отдадим что нибудь, все равно еще нужно узнать куда сохранилось прежде чем перемещать файлы
        return implode(',', $this->values);
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
            if (file_exists(IMPORT_FILEPATH . self::FolderImportFiles . \DIRECTORY_SEPARATOR . $file) and file_exists($sNewPath . $fileName)) {
                unlink($sNewPath . $fileName);
            }
            try {
                $bRes = copy(IMPORT_FILEPATH . self::FolderImportFiles . \DIRECTORY_SEPARATOR . $file, $sNewPath . $fileName);
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
