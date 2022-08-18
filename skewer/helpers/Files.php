<?php

namespace skewer\helpers;

use Exception;
use skewer\base\site\Site;

/**
 * Класс работы с файловой системой.
 */
class Files
{
    /**
     * Используется для поиска по шаблону - указывает на необходимость поиска только файлов.
     */
    const FILES = 0x01;

    /**
     * Используется для поиска по шаблону - указывает на необходимость поиска только директорий.
     */
    const DIRS = 0x02;

    /**
     * Экземпляр класса.
     *
     * @var null|Files
     */
    private static $oInstance = null;

    /**
     * Путь до корневой публичной директории загрузки файлов.
     *
     * @var string
     */
    private static $sUploadPath = '';

    /**
     * Путь до корневой закрытой директории загрузки файлов.
     *
     * @var string
     */
    private static $sUploadProtectedPath = '';

    /**
     * Закрытый конструктор
     */

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function __construct()
    {
    }

    // constructor

    /**
     * Устанавливает начальные значения, требуемые для работы класса.
     *
     * @static
     *
     * @param string $sFilePath Путь до корневой публичной директории загрузки файлов
     * @param string $sProtectedFilePath Путь до корневой закрытой директории загрузки файлов
     *
     * @return null|Files
     */
    public static function init($sFilePath, $sProtectedFilePath)
    {
        if (isset(self::$oInstance) and (self::$oInstance instanceof self)) {
            self::registerPaths($sFilePath, $sProtectedFilePath);

            return self::$oInstance;
        }

        self::$oInstance = new self();
        self::registerPaths($sFilePath, $sProtectedFilePath);

        return self::$oInstance;
    }

    // func

    /**
     * Устанавливает пути до корневых директорий загруprb файлов.
     *
     * @static
     *
     * @param string $sFilePath Путь до корневой публичной директории загрузки файлов
     * @param string $sProtectedFilePath Путь до корневой закрытой директории загрузки файлов
     */
    protected static function registerPaths($sFilePath, $sProtectedFilePath)
    {
        self::$sUploadPath = $sFilePath;
        self::$sUploadProtectedPath = $sProtectedFilePath;
    }

    // func

    /**
     * Удаляет файл $sFileName.
     *
     * @static
     *
     * @param string $sFileName удаляемый файл
     *
     * @return bool
     */
    public static function remove($sFileName)
    {
        if (mb_strpos($sFileName, '..') !== false) {
            return false;
        }
        if (!is_file($sFileName)) {
            return false;
        }

        return unlink($sFileName);
    }

    // func

    /**
     * Возвращает расширение файла $sFileName.
     *
     * @static
     *
     * @param string $sFileName полное имя файла
     *
     * @return bool|mixed
     */
    public static function getExtension($sFileName)
    {
        return ($aFile = explode('.', $sFileName)) ? end($aFile) : false;
    }

    // func

    /**
     * Преобразует в URL-валидный формат строку либо имя файла $sFileName. Имя указывается без учета пути.
     *
     * @static
     *
     * @param string $sFileName обрабатываемая строка
     * @param bool $bIsFile Строка может обрабатываться как имя файла так и как строка в зависимости от значения $bIsFile
     * @param bool $bUseSalt Подставлять хеш в конец имени файла
     * @param int $iLength максимальная длина преобразованной строки
     *
     * @return string Возвращает преобразованную строку
     */
    public static function makeURLValidName($sFileName, $bIsFile = true, $bUseSalt = false, $iLength = 25)
    {
        // замена символов
        $sFileName = Transliterate::change($sFileName);
        $sFileName = mb_strtolower(trim($sFileName));

        $sExt = '';

        if ($bIsFile) {
            $sExt = self::getExtension($sFileName);
            $sFileName = basename($sFileName, '.' . $sExt);
        }

        if ($bUseSalt) {
            $sFileName = mb_substr(md5(microtime()), 0, $iLength);
        } else {
            $sFileName = Transliterate::changeDeprecated($sFileName);
            $sFileName = trim(Transliterate::mergeDelimiters($sFileName), Transliterate::getDelimiter());
        }

        return mb_substr($sFileName, 0, $iLength) . (($bIsFile and !empty($sExt)) ? '.' . $sExt : '');
    }

    // func

    /**
     * Проверяет наличие директории $sPath. Если Директория не существует - пытается создать. Если указан $bProtected
     * Проверка ведется в закрытой директории.
     *
     * @static
     *
     * @param string $sPath Путь до проверяемой директории
     * @param bool $bProtected Флаг поиска в закрытой директории
     *
     * @return bool|string
     */
    public static function createFolderPath($sPath, $bProtected = false)
    {
        try {
            $sRootFolder = (!$bProtected) ? self::$sUploadPath : self::$sUploadProtectedPath;

            if (is_dir($sRootFolder . $sPath)) {
                return $sRootFolder . $sPath;
            }

            $aFolders = explode(\DIRECTORY_SEPARATOR, $sPath);
            $aFolders = array_diff($aFolders, ['']);

            if (!count($aFolders)) {
                return false;
            }

            $sCreatedFolder = $sRootFolder;

            foreach ($aFolders as $sFolder) {
                $sFolder = self::makeURLValidName($sFolder, false);

                if (empty($sFolder)) {
                    return false;
                }

                $sCreatedFolder .= $sFolder . \DIRECTORY_SEPARATOR;

                if (is_dir($sCreatedFolder)) {
                    continue;
                }

                if (!@mkdir($sCreatedFolder, 0775, true)) {
                    return false;
                }

                chmod($sCreatedFolder, 0775);
            }// each folder

            if (is_dir($sCreatedFolder)) {
                if (mb_substr($sPath, -1) == \DIRECTORY_SEPARATOR) {
                    return rtrim($sCreatedFolder, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
                }

                return rtrim($sCreatedFolder, \DIRECTORY_SEPARATOR);
            }
            throw new Exception('not created directory');
        } catch (Exception $e) {
            echo $e;

            return false;
        }
    }

    // func

    /**
     * Проверяет наличие директории $sPath. Если Директория не существует - пытается создать.
     *
     * @static
     *
     * @param string $sPath путь и создаваемая директория(поиск ведется от корневой директории сайта)
     *
     * @return bool|string
     */
    public static function makeDirectory($sPath)
    {
        if (!defined('ROOTPATH')) {
            return false;
        }

        $sNewPath = WEBPATH . \DIRECTORY_SEPARATOR . $sPath;

        if (is_dir($sNewPath)) {
            return $sNewPath;
        }

        $aFolders = explode(\DIRECTORY_SEPARATOR, $sPath);
        $aFolders = array_diff($aFolders, ['']);

        if (!count($aFolders)) {
            return false;
        }

        $sCreatedFolder = WEBPATH;

        foreach ($aFolders as $sFolder) {
            $sFolder = self::makeURLValidName($sFolder, false);

            if (empty($sFolder)) {
                return false;
            }

            $sCreatedFolder .= $sFolder . \DIRECTORY_SEPARATOR;

            if (is_dir($sCreatedFolder)) {
                continue;
            }

            if (!@mkdir($sCreatedFolder)) {
                return false;
            }

            chmod($sCreatedFolder, 0775);
        }// each folder

        return $sCreatedFolder;
    }

    // func

    /**
     * Перемещает папку раздела из закрытой директории в открытую.
     *
     * @static
     *
     * @param int $iSectionId Id раздела
     *
     * @return bool
     */
    public static function moveToPublic($iSectionId)
    {
        if (!is_dir(self::$sUploadProtectedPath . $iSectionId)) {
            return false;
        }

        $sNewPath = self::createFolderPath($iSectionId . \DIRECTORY_SEPARATOR, false);

        return rename(self::$sUploadProtectedPath . $iSectionId, $sNewPath);
    }

    // func

    /**
     * Перемещает папку раздела из открытой директории в закрытую.
     *
     * @static
     *
     * @param int $iSectionId Id раздела
     *
     * @return bool
     */
    public static function moveToPrivate($iSectionId)
    {
        if (!is_dir(self::$sUploadPath . $iSectionId)) {
            return false;
        }

        $sNewPath = self::createFolderPath($iSectionId . \DIRECTORY_SEPARATOR, true);

        return rename(self::$sUploadPath . $iSectionId, $sNewPath);
    }

    // func

    /**
     * Возвращает список файлов из директории раздела $iSectionId.
     *
     * @static
     *
     * @param $iSectionId
     * @param string $sFolderName Если указана поддиректория - возвращается список файлов из  $iSectionId/$sFolderName
     * @param bool $bProtected Поиск производится в закрытой директории
     * @param array $aFileTypes Содержит массив расширений файлов, требуемых в результирующей выборке
     * @param string $sSortBy содержит ключ поля результирующего массива, по которому требуется сортировка. Может принимать следующие значения:
     * name, size, modifyDate
     * @param string $sDirection Содержит направление сортировки Может принимать два значения: ASC и DESC
     * @param bool $bWithDirs Указывает на необходимость выборки поддиректорий в результирующий список
     *
     * @return array|bool Возвращает массив найденных файлов либо false если директория не найдена
     */
    public static function getFilesFromSection($iSectionId, $sFolderName = '', $bProtected = false, $aFileTypes = [], $sSortBy = 'modifyDate', $sDirection = 'ASC', $bWithDirs = false)
    {
        $aOut = [];

        $sFolder = (!$bProtected) ? self::$sUploadPath : self::$sUploadProtectedPath;

        $sFolder .= $iSectionId . \DIRECTORY_SEPARATOR;

        if ($sFolderName) {
            $sFolder .= $sFolderName . \DIRECTORY_SEPARATOR;
        }

        if (!is_dir($sFolder)) {
            return false;
        }

        $oDir = dir($sFolder);

        /* @noinspection PhpUndefinedFieldInspection */
        if ($oDir->handle) {
            while (false !== ($sFile = $oDir->read())) {
                $aFile = [];

                if ($sFile == '.' and $sFile == '..') {
                    continue;
                }

                $sFilePath = $sFolder . $sFile;

                if (!$bWithDirs and is_dir($sFilePath)) {
                    continue;
                }

                $sExt = self::getExtension($sFile);

                if (empty($sExt)) {
                    continue;
                }
                if (count($aFileTypes) and !in_array($sExt, $aFileTypes)) {
                    continue;
                }

                $aFile['name'] = $sFile;
                $aFile['size'] = self::getFileSize($sFilePath);
                $aFile['ext'] = $sExt;
                $aFile['modifyDate'] = date('Y-m-d H:i:s', filemtime($sFilePath));
                $aFile['webPath'] = str_ireplace(WEBPATH, Site::httpDomainSlash(), $sFilePath);
                $aFile['webPathShort'] = str_ireplace(WEBPATH, '/', $sFilePath);
                $aFile['serverPath'] = $sFilePath;
                $aOut[] = $aFile;
            }
        }// h

        $oDir->close();

        usort($aOut, static function ($aFirst, $aSecond) use ($sSortBy, $sDirection) {
            $iOut = 1;
            switch ($sSortBy) {
                case 'name':
                    $iOut = strcmp($aFirst['name'], $aSecond['name']);
                    break;
                case 'size':
                    $iOut = ($aFirst['size'] > $aSecond['size']) ? 1 : -1;
                    break;
                case 'modifyDate':
                    $iOut = ($aFirst['modifyDate'] > $aSecond['modifyDate']) ? 1 : -1;
                    break;
            }

            return ($sDirection == 'DESC') ? $iOut * (-1) : $iOut;
        });

        return $aOut;
    }

    // func

    /**
     * Возвращает адаптированный размер файлаю.
     *
     * @static
     *
     * @param string $sFilePath Полный путь к файлу
     *
     * @return bool|string Возвращает строку размера либо false если файл не найден
     */
    public static function getFileSize($sFilePath)
    {
        if (!file_exists($sFilePath)) {
            return false;
        }

        $iFileSize = filesize($sFilePath);

        return self::sizeToStr($iFileSize);
    }

    // func

    /**
     * Проверяет существование директории.
     *
     * @static
     *
     * @param $iSectionId - раздел
     * @param string $sFolderName - директория
     * @param bool $bProtected - флаг "закрытая часть"
     *
     * @return string - валидное имя существующей папки или ''
     */
    public static function checkFilePath($iSectionId, $sFolderName = '', $bProtected = false)
    {
        $sFolder = (!$bProtected) ? self::$sUploadPath : self::$sUploadProtectedPath;

        $sFolder .= $iSectionId . \DIRECTORY_SEPARATOR . $sFolderName;

        if ($sFolderName) {
            $sFolder .= \DIRECTORY_SEPARATOR;
        }

        return is_dir($sFolder) ? $sFolder : '';
    }

    /**
     * Возвращает полный путь по параметрам загрузки.
     *
     * @static
     *
     * @param $iSectionId - раздел
     * @param string $sFolderName - директория
     * @param bool $bProtected - флаг "закрытая часть"
     *
     * @return string - валидное имя существующей папки или ''
     */
    public static function getFilePath($iSectionId, $sFolderName = '', $bProtected = false)
    {
        $sFolder = (!$bProtected) ? self::$sUploadPath : self::$sUploadProtectedPath;

        $sFolder .= $iSectionId . \DIRECTORY_SEPARATOR . $sFolderName . '/';

        return $sFolder;
    }

    /**
     * Удаляет открытые и закрытые  директории загруженный файлов раздела $iSectionId.
     *
     * @static
     *
     * @param int $iSectionId Id удаляемого раздела
     *
     * @return bool
     */
    public static function delSection($iSectionId)
    {
        $sSectionDir = self::$sUploadPath . $iSectionId . \DIRECTORY_SEPARATOR;
        $sPrivateSectionDir = self::$sUploadProtectedPath . $iSectionId . \DIRECTORY_SEPARATOR;

        self::delDirectoryRec($sSectionDir);
        self::delDirectoryRec($sPrivateSectionDir);

        return true;
    }

    // func

    /**
     * Удаляет директорию $sDirectory, включая все ее содержимое.
     *
     * @static
     *
     * @param string $sDirectory Путь к удаляемой директории
     * @param bool $bRemoveRootDirectory Если сброшен флаг, то корневая директория не удаляется
     *
     * @return bool Возвращает true в случае успешного завершения либо false в случае ошибки
     */
    public static function delDirectoryRec($sDirectory, $bRemoveRootDirectory = true)
    {
        if (!is_dir($sDirectory)) {
            return false;
        }

        $aFiles = scandir($sDirectory);
        foreach ($aFiles as $sFile) {
            if ($sFile == '.' or $sFile == '..') {
                continue;
            }
            $result = true;
            if (filetype($sDirectory . $sFile) == 'dir') {
                self::delDirectoryRec($sDirectory . $sFile . \DIRECTORY_SEPARATOR);
            } else {
                $result = unlink($sDirectory . $sFile);
            }

            if (!$result) {
                return false;
            }
        }
        reset($aFiles);
        if ($bRemoveRootDirectory) {
            rmdir($sDirectory);
        }

        return true;
    }

    // func

    /**
     * Генерирует заведомо уникальное для директории $sDirectoryPath имя файла.
     *
     * @static
     *
     * @param $sDirectoryPath
     * @param $sFileName
     * @param int $iCounterLimit
     *
     * @throws Exception
     *
     * @return bool|string Возвращает новое имя либо false
     */
    public static function generateUniqFileName($sDirectoryPath, $sFileName, $iCounterLimit = 100)
    {
        try {
            $sFileName = Files::makeURLValidName($sFileName, true, false);
            $v = strtotime(date('Y-m-d H:i:s'));
            if ($iPos = mb_strpos($sFileName, '.')) {
                $sFileName = mb_substr($sFileName, 0, $iPos) . "_{$v}" . mb_substr($sFileName, $iPos);
            } else {
                $sFileName = $sFileName . "_{$v}";
            }
            $sBaseFileName = $sFileName;
            $i = 1;
            if (file_exists($sDirectoryPath . $sFileName)) {
                while (file_exists($sDirectoryPath . $sFileName)) {
                    if ($iPos = mb_strpos($sBaseFileName, '.')) {
                        $sFileName = mb_substr($sBaseFileName, 0, $iPos) . "_{$i}" . mb_substr($sBaseFileName, $iPos);
                    } else {
                        $sFileName = $sBaseFileName . "_{$i}";
                    }
                    ++$i;
                    if ($i > $iCounterLimit) {
                        throw new Exception('Error: Name is not generated!');
                    }
                }
            }
        } catch (Exception $e) {
            echo $e;

            return false;
        }

        return $sDirectoryPath . $sFileName;
    }

    // func

    /**
     * Возвращает путь до корневой директории для загрузки файлов.
     *
     * @static
     *
     * @param bool $bProtected Указывает на режим (защищенная директория или нет)
     *
     * @return string
     */
    public static function getRootUploadPath($bProtected = false)
    {
        return ($bProtected) ? PRIVATE_FILEPATH : FILEPATH;
    }

    // func

    /**
     * Возвращает WEB путь собранный из root.
     *
     * @static
     *
     * @param string $sSystemPath Путь относительно web корня сервера
     * @param bool $bWithSiteAddress Указывает на необходимость замены включая домен
     *
     * @return mixed
     */
    public static function getWebPath($sSystemPath, $bWithSiteAddress = true)
    {
        $sReplacment = ($bWithSiteAddress) ? Site::httpDomainSlash() : '/';

        return str_replace(WEBPATH, $sReplacment, $sSystemPath);
    }

    // func

    /**
     * Отдает максимальный допустимый размер для пакета загрузки в байтах.
     *
     * @static
     *
     * @return int
     */
    public static function getMaxUploadSize()
    {
        $iPostMaxSize = static::strToByte(ini_get('post_max_size'));
        $iUploadMaxFilesize = static::strToByte(ini_get('upload_max_filesize'));
        $iMemoryLimit = static::strToByte(ini_get('memory_limit'));

        $aCompare = [];
        if ($iPostMaxSize > 0) {
            $aCompare[] = $iPostMaxSize;
        }
        if ($iUploadMaxFilesize > 0) {
            $aCompare[] = $iUploadMaxFilesize;
        }
        if ($iMemoryLimit > 0) {
            $aCompare[] = $iMemoryLimit;
        }

        if (empty($aCompare)) {
            return 0;
        }

        return min($aCompare);
    }

    /**
     * Принимает параметры ini настроек вида "128M"
     * и переводит значение в байты.
     *
     * @param string $sString
     *
     * @return int
     */
    public static function strToByte($sString)
    {
        $sString = trim($sString);
        $cLast = mb_strtolower($sString[mb_strlen($sString) - 1]);
        $iString = (int) $sString;
        switch ($cLast) {
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $iString *= 1024;
            /* @noinspection PhpMissingBreakStatementInspection */
            // no break
            case 'm':
                $iString *= 1024;
                // no break
            case 'k':
                $iString *= 1024;
        }

        return $iString;
    }

    /**
     * Переводит размер в байтах с строку с размером
     *
     * @static
     *
     * @param int $iBytes
     *
     * @return string
     */
    public static function sizeToStr($iBytes)
    {
        if ($iBytes >= 1073741824) {
            return number_format($iBytes / 1073741824, 2) . ' GB';
        }

        if ($iBytes >= 1048576) {
            return number_format($iBytes / 1048576, 2) . ' MB';
        }

        if ($iBytes >= 1024) {
            return number_format($iBytes / 1024, 2) . ' KB';
        }

        return $iBytes . ' B';
    }

    /**
     * Отдает строку для сортировки размеров в списке.
     *
     * @param int $iBytes
     *
     * @return string
     */
    public static function sizeToSortStr($iBytes)
    {
        if (!$iBytes) {
            return '0000.00';
        }

        return sprintf(
            '%d%06.2f',
            $i = (int) floor(log($iBytes, 1024)),
            round($iBytes / 1024 ** ($i), 2)
        );
    }

    /**
     * Возвращает список файлов и диреторий, находящихся по пути $directory в FS.
     *
     * @param string $directory Путь к директории поиска
     * @param bool $absolutePath Если указано true то массив результатов будет содержать абсолютные пути
     * @param int $options Режим фильтрации содержимого:
     * Files::FILES - только файлы
     * Files::DIRS - только директории
     * Поддерживает побитовые операции
     *
     * @return array|bool
     */
    public static function getDirectoryContent($directory, $absolutePath = true, $options = self::FILES)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = scandir($directory);

        $list = [];
        foreach ($files as $file) {
            if ($file == '.' or $file == '..') {
                continue;
            }

            $fileType = filetype($directory . $file);

            if ($fileType == 'file' && ($options & self::FILES)) {
                $list[] = ($absolutePath) ? $directory . $file : $file;
            }

            if ($fileType == 'dir' && ($options & self::DIRS)) {
                $list[] = ($absolutePath) ? $directory . $file : $file;
            }
        }

        return $list;
    }
}
